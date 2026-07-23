<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\JenisLaporan;
use App\Models\Kecamatan;
use App\Utils\Keuangan;
use App\Models\PinjamanIndividu;
use App\Models\RealSimpanan;
use App\Models\Rekap;
use App\Models\Rekening;
use App\Models\Simpanan;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Session;

class RekapController extends Controller
{
    public function index()
    {
        $keuangan = new Keuangan;
        $tahun = date('Y');
        $bulan = date('m');

        $id = Session::get('id_rekap');
        $saldo_kec = [];
        $rekap = Rekap::where('id', $id)->first();

        $lokasiIds = array_filter(explode(',', $rekap->lokasi));
        $kdKecList = Kecamatan::whereIn('id', $lokasiIds)->pluck('kd_kec');
        $kecamatan = Kecamatan::whereIn('kd_kec', $kdKecList)
            ->select('id', 'kd_kec as kode', 'nama_kec as nama')
            ->orderBy('nama_kec', 'ASC')
            ->get();
        $total = new \stdClass();
        $total->total_a = 0;
        $total->anggota = 0;
        $total->total_p = 0;
        $total->total_v = 0;
        $total->total_w = 0;

        $total->total_1 = 0;
        $total->total_2 = 0;
        $total->total_21 = 0;
        $total->total_3 = 0;
        $total->total_t = 0;

        foreach ($kecamatan as $wl) {
            $saldo_kec[$wl->kode] = [
                'nama' => $wl->nama,
                'kode' => $wl->kode,
                'laba_rugi' => [
                    'pendapatan' => 0,
                    'biaya' => 0,
                ],
                'surplus' => 0,
                'used_dbm' => false
            ];

            if ($wl->kode) {
                Session::put('lokasi', $wl->id);

                $angg       = Anggota::count();

                $pinj_i   = PinjamanIndividu::where('jenis_pinjaman', 'I');
                $angg       = Anggota::count();
                $jumlah_a = $pinj_i->where('status', 'A')->count();
                $jumlah_p = $pinj_i->where('status', 'P')->count();
                $jumlah_v = $pinj_i->where('status', 'V')->count();
                $jumlah_w = $pinj_i->where('status', 'W')->count();

                $total->total_a += $jumlah_a;
                $total->anggota += $angg;
                $total->total_p += $jumlah_p;
                $total->total_v += $jumlah_v;
                $total->total_w += $jumlah_w;

                $startDate = \Carbon\Carbon::now()->subMonth();
                $simp     = Simpanan::where('status', 'A')
                    ->with('realSimpananTerbesar')->get();
                $jumlah_1 = $simp->where('jenis_simpanan', '1')->count();
                $jumlah_2 = $simp->where('jenis_simpanan', '2')->count();
                $jumlah_21 = $simp->filter(function ($item) use ($startDate) {
                    return $item->jenis_simpanan == '2'
                        && $item->realSimpananTerbesar
                        && $item->realSimpananTerbesar->tgl_transaksi >= $startDate;
                })->count();

                $jumlah_3 = $simp->where('jenis_simpanan', '3')->count();
                $jumlah_t = $simp->count();

                $total->total_1 += $jumlah_1;
                $total->total_2 += $jumlah_2;
                $total->total_21 += $jumlah_21;
                $total->total_3 += $jumlah_3;
                $total->total_t += $jumlah_t;

                $laba_rugi = Rekening::where('lev1', '>=', '4')->with([
                    'kom_saldo' => function ($query) use ($tahun, $bulan) {
                        $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                            $query->where('bulan', '0')->orwhere('bulan', $bulan);
                        });
                    },
                    'saldo' => function ($query) use ($tahun, $bulan) {
                        $query->where([
                            ['tahun', $tahun],
                            ['bulan', ($bulan - 1)]
                        ]);
                    }
                ])->orderBy('kode_akun', 'ASC')->get();

                $pendapatan = 0;
                $biaya = 0;
                foreach ($laba_rugi as $lb) {
                    $saldo = $keuangan->komSaldo($lb);
                    if ($lb->lev1 == 5) {
                        $biaya += $saldo;
                    } else {
                        $pendapatan += $saldo;
                    }
                }

                $saldo_kec[$wl->kode]['laba_rugi'] = [
                    'pendapatan' => $pendapatan,
                    'biaya' => $biaya,
                ];

                $saldo_kec[$wl->kode]['surplus'] = $pendapatan - $biaya;
                $saldo_kec[$wl->kode]['used_dbm'] = true;
            }
        }

        $title = Session::get('nama_rekap') . ' Page';
        $api = env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com');
        $api_key = env('WA_GATEWAY_API_KEY');

        $wa = \App\Models\Whatsapp::where('lokasi', Session::get('lokasi'))->first();
        $wa_instance_name = $wa->instance_name ?? null;

        return view('rekap.index')->with(compact('title', 'saldo_kec', 'total', 'keuangan', 'api', 'api_key', 'wa_instance_name'));
    }

    public function tandaTangan()
    {
        $kd_rekap = Session::get('kd_rekap');
        $rekap = Rekap::where('kd_rekap', $kd_rekap)->first();

        $title = 'Pengaturan Tanda Tangan Laporan';
        return view('rekap.tanda_tangan')->with(compact('title', 'rekap'));
    }

    public function simpanTandaTangan(Request $request)
    {
        $data = $request->only([
            'tanda_tangan'
        ]);

        $data['tanda_tangan'] = preg_replace('/<table[^>]*>/', '<table class="p0" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">', $data['tanda_tangan'], 1);
        $data['tanda_tangan'] = preg_replace('/height:\s*[^;]+;?/', '', $data['tanda_tangan']);

        $data['tanda_tangan'] = str_replace('colgroup', 'tr', $data['tanda_tangan']);
        $data['tanda_tangan'] = preg_replace('/<col([^>]*)>/', '<td$1>&nbsp;</td>', $data['tanda_tangan']);

        $kd_rekap = Session::get('kd_rekap');
        $tanda_tangan = Rekap::where('kd_rekap', $kd_rekap)->update([
            'tanda_tangan' => json_encode($data['tanda_tangan'])
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Tanda Tangan Berhasil diperbarui'
        ]);
    }

    public function kecamatan($kd_kec)
    {

        $kec = Kecamatan::where('kd_kec', $kd_kec)->with('kabupaten')->first();
        $laporan = JenisLaporan::where('file', '!=', '0')->orderBy('urut', 'ASC')->get();

        Session::put('lokasi', $kec->id);
        if (!$kec) {
            $kec = Wilayah::where('kode', $kd_kec)->first();

            $title = 'Kecamatan Belum Terdaftar';
            return view('rekap._kecamatan')->with(compact('title', 'kec'));
        }

        $kab = $kec->kabupaten;
        $nama_kec = $kec->sebutan_kec . ' ' . $kec->nama_kec;
        if (Keuangan::startWith($kab->nama_kab, 'KOTA') || Keuangan::startWith($kab->nama_kab, 'KAB')) {
            $nama_kec .= ' ' . ucwords(strtolower($kab->nama_kab));
        } else {
            $nama_kec .= ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
        }

        Session::put('lokasi', $kec->id);
        $title = 'Pelaporan ' . $kec->sebutan_kec . ' ' . $kec->nama_kec;
        return view('rekap.kecamatan')->with(compact('title', 'kec', 'laporan', 'nama_kec'));
    }
    public function laporan()
    {
        $id = Session::get('id_rekap');
        $rekap = Rekap::where('id', $id)->first();
        $title = 'Laporan Rekap';
        $laporan = collect([
            (object)[
                'id' => 1,
                'urut' => 1,
                'nama_laporan' => 'Neraca Detail',
                'file' => 'rekap_neraca',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 5,
                'urut' => 1,
                'nama_laporan' => 'Neraca Rekap',
                'file' => 'rekap_neraca2',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 2,
                'urut' => 2,
                'nama_laporan' => 'Laba Rugi Detail',
                'file' => 'rekap_rb',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 6,
                'urut' => 2,
                'nama_laporan' => 'Laba Rugi Rekap',
                'file' => 'rekap_rb2',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 3,
                'urut' => 3,
                'nama_laporan' => 'Catatan Atas Laporan Keuangan Detail',
                'file' => 'rekap_calk',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 3,
                'urut' => 3,
                'nama_laporan' => 'Catatan Atas Laporan Keuangan Rekap',
                'file' => 'rekap_calk2',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 6,
                'urut' => 3,
                'nama_laporan' => 'Laporan Perubahan Modal',
                'file' => 'rekap_modal',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 4,
                'urut' => 4,
                'nama_laporan' => 'Arus Kas Detail',
                'file' => 'rekap_arus_kas_v1',
                'awal_tahun' => 0,
            ],
            (object)[
                'id' => 5,
                'urut' => 5,
                'nama_laporan' => 'Arus Kas Rekap',
                'file' => 'rekap_arus_kas_v2',
                'awal_tahun' => 0,
            ],
        ]);

        return view('rekap.laporan')->with(compact('title', 'rekap', 'laporan'));
    }
}
