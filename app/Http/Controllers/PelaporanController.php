<?php

namespace App\Http\Controllers;

use App\Models\AdminInvoice;
use App\Models\AkunLevel1;
use App\Models\AkunLevel2;
use App\Models\AkunLevel3;
use App\Models\Anggota;
use App\Models\ArusKas;
use App\Models\ArusKasLkm;
use App\Models\MasterArusKas;
use App\Models\Calk;
use App\Models\Desa;
use App\Models\Rekap;
use App\Models\JenisLaporan;
use App\Models\JenisLaporanPinjaman;
use App\Models\JenisProdukPinjaman;
use App\Models\JenisSimpanan;
use App\Models\SimpananAnggota;
use App\Models\Simpanan;
use App\Models\Kecamatan;
use App\Models\Kelompok;
use App\Models\Lkm;
use App\Models\RekeningOjk;
use App\Models\PinjamanKelompok;
use App\Models\PinjamanIndividu;
use App\Models\PinjamanAnggota;
use App\Models\Rekening;
use App\Models\SubLaporan;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\User;
use App\Utils\ArusKas as UtilsArusKas;
use App\Utils\Calk as UtilsCalk;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use DB;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use PDF;
use Session;

class PelaporanController extends Controller
{
    public function index()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $laporan = JenisLaporan::where([['file', '!=', '0']])->orderBy('urut', 'ASC')->get();

        $title = 'Pelaporan';
        return view('pelaporan.index')->with(compact('title', 'kec', 'laporan'));
    }

    public function subLaporan($file)
    {
        if ($file == 3) {
            $rekening = Rekening::where('kode_akun', '!=', '3.2.02.01')->orderBy('kode_akun', 'ASC')->get();
            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'rekening'));
        }

        if ($file == 20) {
            $ojk = SubLaporan::where('file', '!=', '0')
                ->orderBy('urut')
                ->orderBy('id')
                ->get();
            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'ojk'));
        }

        if ($file == 21) {
            $ojk = SubLaporan::where('file_kab', '!=', '0')
                ->orderBy('urut')
                ->orderBy('id')
                ->get();
            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'ojk'));
        }

        if ($file == 'calk') {
            $tahun = request()->get('tahun');
            $bulan = request()->get('bulan');

            $calk = Calk::where([
                ['lokasi', Session::get('lokasi')],
                ['tanggal', 'LIKE', $tahun . '-' . $bulan . '%']
            ])->first();

            $keterangan = '';
            if ($calk) {
                $keterangan = $calk->catatan;
            }

            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'keterangan'));
        }

        if ($file == 5) {
            $lokasiUser = (string) Session::get('lokasi');
            $jenis_laporan = JenisLaporanPinjaman::where('file', '!=', '0')
                ->where(function ($q) use ($lokasiUser) {
                    $q->where('lokasi', '0')
                        ->orWhereRaw("FIND_IN_SET(?, REPLACE(lokasi, ' ', ''))", [$lokasiUser]);
                })
                ->orderBy('urut', 'ASC')
                ->get();

            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'jenis_laporan'));
        }

        if ($file == 14) {
            $data = [
                0 => [
                    'title' => '01. Januari - Maret',
                    'id' => '1,2,3'
                ],
                1 => [
                    'title' => '02. April - Juni',
                    'id' => '4,5,6'
                ],
                2 => [
                    'title' => '03. Juli - September',
                    'id' => '7,8,9'
                ],
                3 => [
                    'title' => '04. Oktober - Desember',
                    'id' => '10,11,12'
                ],
                4 => [
                    'title' => '05. Januari - Desember',
                    'id' => '12'
                ]
            ];

            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'data'));
        }

        if ($file == 'tutup_buku') {
            $data = [
                0 => [
                    'title' => 'Pembagian Laba',
                    'file' => 'alokasi_laba'
                ],
                1 => [
                    'title' => 'Jurnal Tutup Buku',
                    'file' => 'jurnal_tutup_buku'
                ],
                2 => [
                    'title' => 'Neraca',
                    'file' => 'neraca_tutup_buku'
                ],
                3 => [
                    'title' => 'Laba Rugi',
                    'file' => 'laba_rugi_tutup_buku'
                ],
                4 => [
                    'title' => 'CALK',
                    'file' => 'CALK_tutup_buku'
                ]
            ];

            return view('pelaporan.partials.sub_laporan')->with(compact('file', 'data'));
        }

        return view('pelaporan.partials.sub_laporan')->with(compact('file'));
    }

    private function penduduk(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['desa'] = Desa::where('kd_kec', $data['kec']->kd_kec)->with([
            'anggota',
            'anggota.u',
            'sebutan_desa'
        ])->get();

        $view = view('pelaporan.view.basis_data.penduduk', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function preview(Request $request, $lokasi = null)
    {
        if ($lokasi != null) {
            Session::put('lokasi', $lokasi);
        }
        $data = $request->only([
            'tahun',
            'bulan',
            'hari',
            'laporan',
            'sub_laporan',
            'type'
        ]);

        if (strpos($data['laporan'], 'rekap_') === 0) {
            $lokasi_ids = session('rekapan');
            $lokasi_ids = explode(',', $lokasi_ids);
            $lokasi_ids = array_map('trim', $lokasi_ids);

            if (!empty($lokasi_ids)) {
                Session::put('lokasi', $lokasi_ids[0]);
            }
            $rekap = Rekap::where('id', Session::get('id_rekap'))->first();
            $data['nama_rekap'] = $rekap->nama_rekap;
        }

        if ($data['laporan'] == 'calk' && strlen($data['sub_laporan']) > 22) {
            Calk::where([
                ['lokasi', Session::get('lokasi')],
                ['tanggal', 'LIKE', $data['tahun'] . '-' . $data['bulan'] . '%']
            ])->delete();

            Calk::create([
                'lokasi' => Session::get('lokasi'),
                'tanggal' => $data['tahun'] . '-' . $data['bulan'] . '-01',
                'catatan' => $data['sub_laporan'],
            ]);
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with([
            'kabupaten',
            'desa',
            'saham',
            'desa.saldo' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']]
                ]);
            },
            'ttd'
        ])->first();
        $lkm = Lkm::where('lokasi', $kec->id)->first();

        $kab = $kec->kabupaten;

        $jabatan = '1';
        $level = '1';

        $dir = User::where([
            ['lokasi', Session::get('lokasi')],
            ['jabatan', $jabatan],
            ['level', $level],
            ['sejak', '<=', date('Y-m-t', strtotime($request->tahun . '-' . $request->bulan . '-01'))]
        ])->first();

        $data['logo'] = $kec->logo;
        $data['nama_lembaga'] = $kec->nama_lembaga_sort;
        $data['nama_kecamatan'] = $kec->sebutan_kec . ' ' . $kec->nama_kec;

        if (Keuangan::startWith($kab->nama_kab, 'KOTA') || Keuangan::startWith($kab->nama_kab, 'KAB')) {
            $data['nama_kecamatan'] .= ' ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kabupaten'] = ucwords(strtolower($kab->nama_kab));
        } else {
            $data['nama_kecamatan'] .= ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kabupaten'] = ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
        }

        $data['nomor_usaha'] = 'SK Kemenkumham RI No.' . $kec->nomor_bh;
        $data['info'] = $kec->alamat_kec . ', Telp.' . $kec->telpon_kec;
        $data['email'] = $kec->email_kec;
        $data['lkm'] = $lkm;

        $data['kec'] = $kec;
        $data['desa'] = $kec->desa;
        $data['kab'] = $kab;
        $data['dir'] = $dir;

        if ($data['tahun'] == null) {
            abort(404);
        }

        $data['bulanan'] = true;
        if ($data['bulan'] == null) {
            $data['bulanan'] = false;
            $data['bulan'] = '12';
        }

        $data['harian'] = true;
        if ($data['hari'] == null) {
            $data['harian'] = false;
            $data['hari'] = date('t', strtotime($data['tahun'] . '-' . $data['bulan'] . '-01'));
        }

        $data['tgl_kondisi'] = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];

        if (!$data['bulanan']) {
            $data['bulan'] = null;
            $data['hari'] = null;
            $data['tgl_kondisi'] = $data['tahun'] . '-12-31';
        }
        $data['tanggal_kondisi'] = $kec->nama_kec . ', ' . Tanggal::tglLatin($data['tgl_kondisi']);

        $file = $request->laporan;
        if ($file == 3) {
            $laporan = explode('_', $request->sub_laporan);
            $file = $laporan[0];
            $data['kode_akun'] = $laporan[1];
            $data['laporan'] = 'buku_besar ' . $laporan[1];
            $result = $this->$file($data);
        } elseif ($file == 20 || $file == 21) {
            $file = $request->sub_laporan;
            $result = $this->$file($data);
        } elseif ($file == 5) {
            $file = $request->sub_laporan;
            $data['laporan'] = $file;
            $result = $this->$file($data);
        } elseif ($file == 14) {
            $laporan = explode('_', $request->sub_laporan);
            $file = $laporan[0];
            $data['sub'] = $laporan[1];
            $data['laporan'] = 'E - Budgeting ';
            $result = $this->$file($data);
        } elseif ($file == 'tutup_buku') {
            $file = $request->sub_laporan;
            $result = $this->$file($data);
        } else {
            $result = $this->$file($data);
        }

        if ($data['type'] == 'pdf') {
            return $result;
        }

        if (is_string($result)) {
            $html = $result;
            if (ob_get_length()) ob_end_clean();

            $filename = ($request->laporan ?? 'laporan')
                . '_' . $data['tahun']
                . ($data['bulanan'] ? '_' . $data['bulan'] : '')
                . '.xls';

            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            echo $html;
            exit;
        }
        return $result;
    }

    private function cover(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $view = view('pelaporan.view.cover', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function CV(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['laporan'] = 'Cover';
        $view = view('pelaporan.view.ojk.cover_o', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function PF(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['laporan'] = 'Profil';

        $data['pengurus'] = User::with(['j', 'p'])
            ->where('users.lokasi', Session::get('lokasi'))
            ->whereNotNull('users.jabatan')
            ->join('jabatan', 'users.jabatan', '=', 'jabatan.id')
            ->orderBy('jabatan.urutan', 'asc')
            ->select('users.*')
            ->get();

       $data['dir'] = User::where('users.lokasi', Session::get('lokasi'))
            ->whereNotNull('users.jabatan')
            ->where(function($query) use ($data) {
                if (Session::get('lokasi') == 362) {
                    $query->where('users.jabatan', 1)
                          ->where('users.level', 1);
                } else {
                    $query->where('users.jabatan', $data['jabatan'])
                          ->where('users.level', $data['level'] );
                }
            })
            ->where('users.sejak', '<=', date('Y-m-t', strtotime($data['tahun'] . '-' . $data['bulan'] . '-01')))
            ->join('jabatan', 'users.jabatan', '=', 'jabatan.id')
            ->orderBy('jabatan.urutan', 'asc')
            ->select('users.*')
            ->first();

        $view = view('pelaporan.view.ojk.profil_o', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';
            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function OJKP(array $data)
    {
        $data['keuangan'] = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Per ' . date('t', strtotime($tgl)) . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['debit'] = 0;
        $data['kredit'] = 0;
        $batasId = RekeningOjk::where('parent_id', '0')
            ->where('rekening', 'lr')
            ->min('id') - 1;

        $data['rekening_ojk'] = RekeningOjk::where([
            ['parent_id', '0'],
            ['id', '<=', $batasId]
        ])->with([
            'child',
            'child.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
            'child.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
            'child.child',
            'child.child.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
            'child.child.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
        ])->get();


        $data['laporan'] = 'Neraca';
        $view = view('pelaporan.view.ojk.neraca_ojk', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function DRS(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['kec'] = Kecamatan::find(Session::get('lokasi'));

        $data['jenis_simpanan'] = JenisSimpanan::where(function ($query) {
                $query->where('lokasi', '0')
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'simpanan' => function ($query) use ($data) {
                    $tb_simp = 'simpanan_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_simp'] = $tb_simp;

                    $query->select(
                            $tb_simp . '.*',
                            $tb_angg . '.namadepan',
                            $tb_angg . '.nik',
                            'desa.nama_desa',
                            'desa.kd_desa',
                            'desa.kode_desa',
                            'sebutan_desa.sebutan_desa'
                        )
                        ->join($tb_angg, $tb_simp . '.nia', $tb_angg . '.id')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_s' => function ($query) use ($data) {
                            $tgl_kondisi = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];
                            $query->where('tgl_transaksi', '<', $tgl_kondisi);
                        }], 'real_d')
                        ->withSum(['real_s' => function ($query) use ($data) {
                            $tgl_kondisi = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];
                            $query->where('tgl_transaksi', '<', $tgl_kondisi);
                        }], 'real_k')
                        ->where(function ($query) use ($data, $tb_simp) {
                            $query->where([
                                [$tb_simp . '.status', 'A'],
                                [$tb_simp . '.tgl_buka', '<=', $data['tgl_kondisi']]
                            ])->orWhere([
                                [$tb_simp . '.status', 'L'],
                                [$tb_simp . '.tgl_buka', '<=', $data['tgl_kondisi']],
                                [$tb_simp . '.tgl_tutup', '>=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_simp . '.tgl_buka', 'ASC');
                },
                'simpanan.realSimpananTerbesar' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi'])
                          ->orderBy('id', 'desc');
                },
                'simpanan.trx' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi'])
                          ->where(function ($query) {
                              $query->where('rekening_debit', 'LIKE', '2.2%')
                                  ->orWhere('rekening_kredit', 'LIKE', '2.2%');
                          });
                }
            ])
            ->orderBy('rek_simp', 'ASC')
            ->get();

        $data['laporan'] = 'Rincian Tabungan';
        $view = view('pelaporan.view.ojk.fd_rincian_simpanan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';
            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function LRL(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $awal_tahun = $thn . '-01-01';

        $tgl = $thn . '-' . $bln . '-' . $hari;
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Periode ' . Tanggal::tglLatin($thn . '-' . $bln . '-01') . ' S.D ' . Tanggal::tglLatin($data['tgl_kondisi']);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);

            $data['bulan_lalu'] = date('Y-m-t', strtotime('-1 month', strtotime($thn . '-' . $bln . '-10')));
            $data['header_lalu'] = 'Bulan Lalu';
            $data['header_sekarang'] = 'Bulan Ini';
        } else {
            $data['sub_judul'] = 'Periode ' . Tanggal::tglLatin($awal_tahun) . ' S.D ' . Tanggal::tglLatin($data['tgl_kondisi']);
            $data['tgl'] = Tanggal::tahun($tgl);
            $data['bulan_lalu'] = ($thn - 1) . '-12-31';
            $data['header_lalu'] = 'Tahun Lalu';
            $data['header_sekarang'] = 'Tahun Ini';
        }

        $data['rekening_ojk'] = RekeningOjk::where([
            ['parent_id', '0'],
            ['rekening', '=', 'lr']
        ])->with([
            'child',
            'child.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
            'child.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
            'child.child' => function ($query) {
                $query->where('parent_id', '!=', '0');
            },
            'child.child.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
            'child.child.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan'])->orwhere('bulan', ($data['bulan'] - 1));
                });
            },
        ])->get();

        $data['keuangan'] = $keuangan;
        $data['laporan'] = 'Laba Rugi';
        $view = view('pelaporan.view.ojk.labarugi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';
            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait ');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function DRP(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tahunSaatIni = date('Y');
        $selisihTahun = $tahunSaatIni - $thn;


        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', $tb_angg . '.nik', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>', $data['tgl_kondisi']]
                            ]);
                        })

                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.angsuran_pokok',
                'pinjaman_individu.angsuran_jasa'
            ])
            ->orderBy('kode', 'asc')
            ->get();
        $data['laporan'] = 'Pinjaman Aktif';
        $view = view('pelaporan.view.ojk.daftar_rincian_pinjamanaktif', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function DRPL(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', $tb_angg . '.nik', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.angsuran_pokok',
                'pinjaman_individu.angsuran_jasa'
            ])->get();
        $data['laporan'] = 'Pinjaman Lunas';
        $view = view('pelaporan.view.ojk.rincian_pinjaman_lunas', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function DRPA(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tahunSaatIni = date('Y');
        $selisihTahun = $tahunSaatIni - $thn;


        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', $tb_angg . '.nik', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>', $data['tgl_kondisi']]
                            ]);
                        })

                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.angsuran_pokok',
                'pinjaman_individu.angsuran_jasa'
            ])
            ->orderBy('kode', 'asc')
            ->get();
        $data['laporan'] = 'Pinjaman Aktif';
        $view = view('pelaporan.view.ojk.daftar_rincian_pinjamanagunan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function DRT(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_simpanan'] = JenisSimpanan::with([
            'simpanan' => function ($query) use ($data) {
                $tb_anggota = 'anggota_' . Session::get('lokasi');
                $tb_simpanan = 'simpanan_anggota_' . Session::get('lokasi');

                $query->select($tb_simpanan . '.*', $tb_anggota . '.namadepan', $tb_anggota . '.nik')
                    ->join($tb_anggota, $tb_simpanan . '.nia', $tb_anggota . '.id')
                    ->where('tgl_buka', '<=', $data['tgl_kondisi'],)->where(function ($query) use ($data) {
                        $query->whereRaw('tgl_buka = tgl_tutup')->orwhere('tgl_tutup', '>', $data['tgl_kondisi']);
                    });
            },
            'simpanan.trx' => function ($query) use ($data) {
                $query->where('tgl_transaksi', '<=', $data['tgl_kondisi'])->where(function ($query) {
                    $query->where('rekening_debit', 'LIKE', '2.2%')
                        ->orwhere('rekening_kredit', 'LIKE', '2.2%');
                });
            }
        ])->where('kecuali', 'NOT LIKE', Session::get('lokasi') . '#%')->orwhere('kecuali', 'NOT LIKE', '%#' . Session::get('lokasi'))->get();
        $data['laporan'] = 'Rincian Tabungan';
        $view = view('pelaporan.view.ojk.daftar_rincian_tabungan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function SMPN(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_simpanan'] = JenisSimpanan::with([
            'simpanan' => function ($query) use ($data) {
                $tb_anggota = 'anggota_' . Session::get('lokasi');
                $tb_simpanan = 'simpanan_anggota_' . Session::get('lokasi');

                $query->select($tb_simpanan . '.*', $tb_anggota . '.namadepan', $tb_anggota . '.nik')
                    ->join($tb_anggota, $tb_simpanan . '.nia', $tb_anggota . '.id')
                    ->where('tgl_buka', '<=', $data['tgl_kondisi'],)->where(function ($query) use ($data) {
                        $query->whereRaw('tgl_buka = tgl_tutup')->orwhere('tgl_tutup', '>', $data['tgl_kondisi']);
                    });
            }
        ])->where('kecuali', 'NOT LIKE', Session::get('lokasi') . '#%')->orwhere('kecuali', 'NOT LIKE', '%#' . Session::get('lokasi'))->get();
        $data['laporan'] = 'Simpanan';
        $view = view('pelaporan.view.ojk.simpanan_piutang', $data)->render();
        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function DRPY(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select(
                        $tb_pinj_i . '.*',
                        $tb_angg . '.namadepan',
                        $tb_angg . '.nik',
                        'desa.nama_desa',
                        'desa.kd_desa',
                        'desa.kode_desa',
                        'sebutan_desa.sebutan_desa'
                    )
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa'); // Add closing parenthesis and square bracket here
                }
            ])->get();

        $data['laporan'] = 'Rincian pinjaman Diterima';
        $view = view('pelaporan.view.ojk.rincian_pinjaman_diterima', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function KBP(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_anggota.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_anggota.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.ojk.kolekbilitas_pinjaman2', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pinjaman_diberi(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp_i'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', '.nik', 'agent.agent AS nama_agent', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('agent', $tb_pinj_i . '.id_agent', '=', 'agent.id')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.id_agent', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $data['laporan'] = 'Rincian pinjaman Diberi';
        $view = view('pelaporan.view.ojk.pinjaman_diberi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    private function piutang(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'agent.agent AS nama_agent', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('agent', $tb_pinj_i . '.id_agent', '=', 'agent.id')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.id_agent', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $data['lunas'] = PinjamanIndividu::where([
            ['tgl_lunas', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->with('saldo', 'target')->get();

        $view = view('pelaporan.view.ojk.piutang', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    private function max_suku_bunga(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        if ($data['bulanan']) {
            $data['judul'] = 'Laporan Keuangan';
            $data['sub_judul'] = date('t', strtotime($tgl)) . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with('max_pros')
            ->orderBy('kode', 'ASC')
            ->get();


        $data['laporan'] = 'Laporan Suku Bunga Maksimum Pinjaman';
        $view = view('pelaporan.view.ojk.max_suku_bunga', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function penempatan_dana(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tglLatin($tgl);

        $data['rekening'] = Rekening::where('kode_akun', 'like', '1.1.02%')
            ->with([
                'saldo' => function ($query) use ($thn, $bln) {
                    $query->where('tahun', $thn)
                        ->where('bulan', $bln);
                }
            ])
            ->get();

        $data['laporan'] = 'Daftar Rincian Penempatan Dana';
        $view = view('pelaporan.view.ojk.penempatan_dana', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function KBP2(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp_i'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'agent.agent AS nama_agent', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('agent', $tb_pinj_i . '.id_agent', '=', 'agent.id')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.id_agent', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.ojk.kolekbilitas_pinjaman2', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pcpp(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_anggota.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_anggota.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.ojk.penyisihan_cadangan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function surat_pengantar(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        if (strlen($hari) > 0 && strlen($bln) > 0) {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Harian';
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
        } elseif (strlen($bln) > 0) {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Bulanan';
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin(date('Y-m-t', strtotime($thn . '-' . $bln . '-01')));
            $data['tgl'] = Tanggal::tglLatin(date('Y-m-t', strtotime($thn . '-' . $bln . '-01')));
        } else {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Tahunan';
            $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tahun($tgl);
        }

        $view = view('pelaporan.view.surat_pengantar', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function neraca(array $data)
    {
        $data['keuangan'] = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Per ' . date('t', strtotime($tgl)) . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $view = view('pelaporan.view.neraca', $data)->render();
        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function laba_rugi(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $awal_tahun = $thn . '-01-01';


        $tgl = $thn . '-' . $bln . '-' . $hari;
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Periode ' . Tanggal::tglLatin($thn . '-' . $bln . '-01') . ' S.D ' . Tanggal::tglLatin($data['tgl_kondisi']);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['bulan_lalu'] = date('Y-m-t', strtotime('-1 month', strtotime($thn . '-' . $bln . '-10')));
            $data['header_lalu'] = 'Bulan Lalu';
            $data['header_sekarang'] = 'Bulan Ini';
        } else {
            $data['sub_judul'] = 'Periode ' . Tanggal::tglLatin($awal_tahun) . ' S.D ' . Tanggal::tglLatin($data['tgl_kondisi']);
            $data['tgl'] = Tanggal::tahun($tgl);
            $data['bulan_lalu'] = ($thn - 1) . '-12-31';
            $data['header_lalu'] = 'Tahun Lalu';
            $data['header_sekarang'] = 'Tahun Ini';
        }

        $jenis = 'Tahunan';
        if ($data['bulanan']) {
            $jenis = 'Bulanan';
        }

        $pph = $keuangan->pph($data['tgl_kondisi'], $jenis);
        $laba_rugi = $keuangan->laporan_laba_rugi($data['tgl_kondisi'], $jenis);

        $data['pph'] = [
            'bulan_lalu' => $pph['bulan_lalu'],
            'sekarang' => $pph['bulan_ini']
        ];

        $data['pendapatan'] = $laba_rugi['pendapatan'];
        $data['beban'] = $laba_rugi['beban'];
        $data['pendapatanNOP'] = $laba_rugi['pendapatan_non_ops'];
        $data['bebanNOP'] = $laba_rugi['beban_non_ops'];

        $view = view('pelaporan.view.laba_rugi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function arus_kas(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['jenis'] = 'Tahunan';
        $data['awal'] = 'TAHUN';
        $tgl_lalu = ($thn) . '-00-00';
        if ($data['bulanan'] && ! ($data['laporan'] == '1' || $data['laporan'] == '2')) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['jenis'] = 'Bulanan';
            $data['awal'] = 'BULAN';

            $bulan_lalu = $bln - 1;
            if ($bulan_lalu <= 0) {
                $bulan_lalu = 12;
                $thn -= 1;
            }

            $tgl_lalu = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu . '-01'));
        }

        if ($data['laporan'] == '1') {
            $data['laporan'] = 'Arus Kas Semester I';
            $data['sub_judul'] = 'Semester I Tahun ' . Tanggal::tahun($tgl);
            $data['jenis'] = 'Semester I';
            $data['tgl'] = Tanggal::tglLatin($thn . '-01-01') . ' S.D ' . Tanggal::tglLatin($thn . '-06-30');
        }

        if ($data['laporan'] == '2') {
            $data['laporan'] = 'Arus Kas Semester II';
            $data['sub_judul'] = 'Semester II Tahun ' . Tanggal::tahun($tgl);
            $data['jenis'] = 'Semester II';
            $data['tgl'] = Tanggal::tglLatin($thn . '-07-01') . ' S.D ' . Tanggal::tglLatin($thn . '-12-31');
        }

        $data['keuangan'] = $keuangan;
        $data['arus_kas'] = ArusKas::where('sub', '0')->with('child')->orderBy('id', 'ASC')->get();

        $data['saldo_bulan_lalu'] = $keuangan->saldoKas($tgl_lalu);

        $view = view('pelaporan.view.arus_kas', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function LPM(array $data)
    {
        $data['laporan'] = 'Laporan Perubahan Modal';
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['keuangan'] = $keuangan;
        $data['rekening'] = Rekening::where('lev1', '3')->where('lev2', '1')->with([
            'kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            }
        ])->orderBy('lev1')->orderBy('lev2')->orderBy('lev3', 'DESC')->orderBy('nama_akun')->get();

        $data['rekening2'] = Rekening::where('lev1', '3')->where('lev2', '2')->with([
            'kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            }
        ])->get();

        $view = view('pelaporan.view.perubahan_modal', $data)->render();
        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function CALK(array $data)
    {
        $keuangan = new Keuangan;
        $data['keuangan'] = $keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['nama_tgl'] = 'Tahun ' . $thn;
        $data['sub_judul'] = 'Tahun ' . $thn;
        if ($data['bulanan']) {
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['nama_tgl'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' Tahun ' . $thn;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' Tahun ' . $thn;
        }

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $custom_calk = $data['kec']->custom_calk;
        $decoded_calk = json_decode($custom_calk);
        if ($decoded_calk === null && json_last_error() !== JSON_ERROR_NONE) {
            $decoded_calk = $custom_calk;
        }
        $calk_content = trim((string) $decoded_calk);
        $is_empty_editor = in_array($calk_content, ['', '0', '<p>0</p>', '<p><br></p>', 'null']);

        if ($custom_calk && !$is_empty_editor) {
            $data['view_neraca'] = view('pelaporan.view.partials.neraca_calk', $data)->render();
            $data['view_calk'] = UtilsCalk::calk($data['kec']->custom_calk, $data);
            $view = view('pelaporan.view.calk_custom', $data)->render();
        } else {
            $data['keterangan'] = Calk::where([
                ['lokasi', Session::get('lokasi')],
                ['tanggal', 'LIKE', $data['tahun'] . '-' . $data['bulan'] . '%']
            ])->first();

            $data['sekr'] = User::where([
                ['level', '1'],
                ['jabatan', '2'],
                ['lokasi', Session::get('lokasi')],
            ])->first();

            $data['bend'] = User::where([
                ['level', '1'],
                ['jabatan', '3'],
                ['lokasi', Session::get('lokasi')],
            ])->first();

            $data['pengawas'] = User::where([
                ['level', '3'],
                ['jabatan', '1'],
                ['lokasi', Session::get('lokasi')],
            ])->first();

            $data['saldo_calk'] = Saldo::where([
                ['kode_akun', $data['kec']->kd_kec],
                ['tahun', $thn]
            ])->get();
            $view = view('pelaporan.view.calk', $data)->render();
        }

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function neraca_dana(array $data)
    {
        $data['keuangan'] = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Per ' . date('t', strtotime($tgl)) . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $view = view('pelaporan.view.neraca_dana', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function jurnal_transaksi(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        if (!$data['bulanan']) {
            $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tahun($tgl);
            $data['transaksi'] = Transaksi::whereBetween('tgl_transaksi', [
                $thn . '-01-01',
                $thn . '-12-31'
            ])->where(function ($query) {
                $query->where('rekening_debit', '!=', '0')->orwhere('rekening_kredit', '!=', '0');
            })->with('user', 'rek_debit', 'rek_kredit', 'angs', 'angs.rek_debit', 'angs.rek_kredit')->orderBy('tgl_transaksi', 'ASC')->orderBy('idt', 'ASC')->get();
        } else {
            if (!$data['harian']) {
                $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
                $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
                $data['transaksi'] = Transaksi::whereBetween('tgl_transaksi', [
                    $thn . '-' . $bln . '-01',
                    $thn . '-' . $bln . '-' . date('t', strtotime($thn . '-' . $bln . '-01'))
                ])->where(function ($query) {
                    $query->where('rekening_debit', '!=', '0')->orwhere('rekening_kredit', '!=', '0');
                })->with('user', 'rek_debit', 'rek_kredit', 'angs', 'angs.rek_debit', 'angs.rek_kredit')->orderBy('tgl_transaksi', 'ASC')->orderBy('idt', 'ASC')->get();
            } else {
                $data['sub_judul'] = 'Tanggal ' . $hari . ' Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
                $data['tgl'] = Tanggal::tglLatin($tgl);
                $data['transaksi'] = Transaksi::where('tgl_transaksi', $tgl)->where(function ($query) {
                    $query->where('rekening_debit', '!=', '0')->orwhere('rekening_kredit', '!=', '0');
                })->with('user', 'rek_debit', 'rek_kredit', 'angs', 'angs.rek_debit', 'angs.rek_kredit')->orderBy('tgl_transaksi', 'ASC')->orderBy('idt', 'ASC')->get();
            }
        }

        $view = view('pelaporan.view.jurnal_transaksi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    private function BB(array $data)
    {
        set_time_limit(300);

        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-01-01';
        $data['judul'] = 'Laporan Tahunan';
        $data['sub_judul'] = 'Tahun ' . $thn;
        $data['tgl'] = $thn;
        $awal_bulan = $thn . '00-00';
        $tgl_from = $thn . '-01-01';
        $tgl_to = $thn . '-12-31';
        if ($data['bulanan']) {
            $tgl = $thn . '-' . sprintf('%02d', $bln) . '-';
            $data['judul'] = 'Laporan Bulanan';
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $bulan_lalu = date('m', strtotime('-1 month', strtotime($tgl . '01')));
            $awal_bulan = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu));
            if ($bln == 1) {
                $awal_bulan = $thn . '00-00';
            }
            $tgl_from = $thn . '-' . sprintf('%02d', $bln) . '-01';
            $tgl_to = $thn . '-' . sprintf('%02d', $bln) . '-' . date('t', strtotime($tgl_from));
        }

        if ($data['harian']) {
            $tgl = $thn . '-' . sprintf('%02d', $bln) . '-' . sprintf('%02d', $hari);
            $data['judul'] = 'Laporan Harian';
            $data['sub_judul'] = 'Tanggal ' . $hari . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
            $awal_bulan = $tgl;
            if ($tgl != $thn . '-01-01') {
                $awal_bulan = date('Y-m-d', strtotime('-1 day', strtotime($tgl)));
            }
            $tgl_from = $tgl;
            $tgl_to = $tgl;
        }

        $data['rek'] = Rekening::where('kode_akun', $data['kode_akun'])->first();
        $data['transaksi'] = Transaksi::whereBetween('tgl_transaksi', [$tgl_from, $tgl_to])->where(function ($query) use ($data) {
            $query->where('rekening_debit', $data['kode_akun'])->orwhere('rekening_kredit', $data['kode_akun']);
        })->with([
            'user',
            'kas_angs' => function ($query) {
                $query->where([
                    ['id_pinj_i', '!=', '0'],
                    ['idtp', '!=', '0'],
                    ['rekening_debit', 'NOT LIKE', '1.1.01.01'],
                ]);
            },
        ])->orderBy('tgl_transaksi', 'ASC')->orderBy('urutan', 'ASC')->orderBy('idt', 'ASC')->get();

        $data['saldo'] = $keuangan->saldoAwal($data['tgl_kondisi'], $data['kode_akun']);
        $data['d_bulan_lalu'] = $keuangan->saldoD($awal_bulan, $data['kode_akun']);
        $data['k_bulan_lalu'] = $keuangan->saldoK($awal_bulan, $data['kode_akun']);

        if ($tgl == $thn . '-01-01') {
            $data['d_bulan_lalu'] = '0';
            $data['k_bulan_lalu'] = '0';
        }

        $view = view('pelaporan.view.buku_besar', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');

            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function neraca_saldo(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['keuangan'] = $keuangan;
        $data['rekening'] = Rekening::orderBy('kode_akun', 'ASC')->with([
            'kom_saldo' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            }
        ])->get();

        $view = view('pelaporan.view.neraca_saldo', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function kelompok_aktif(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
                    $tb_kel = 'kelompok_' . $data['kec']->id;
                    $data['tb_pinkel'] = $tb_pinkel;

                    $query->select($tb_pinkel . '.*', $tb_kel . '.nama_kelompok', $tb_kel . '.ketua', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withCount('pinjaman_anggota')
                        ->withSum(['real' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinkel . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.tgl_cair', 'ASC');
                },
                'pinjaman_kelompok.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.kelompok_aktif', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function individu_aktif(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.individu_aktif', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function simpanan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';
        //
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_ps'] = JenisSimpanan::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'simpanan' => function ($query) use ($data) {
                    $tb_simp = 'simpanan_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_simp'] = $tb_simp;

                    $query->select($tb_simp . '.*', $tb_angg . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_simp . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_s' => function ($query) use ($data) {
                            $tgl_kondisi = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];
                            $query->where('tgl_transaksi', '<', $tgl_kondisi);
                        }], 'real_d')
                        ->withSum(['real_s' => function ($query) use ($data) {
                            $tgl_kondisi = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];
                            $query->where('tgl_transaksi', '<', $tgl_kondisi);
                        }], 'real_k')
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_simp'] . '.status', 'A'],
                                [$data['tb_simp'] . '.tgl_buka', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_simp'] . '.status', 'L'],
                                [$data['tb_simp'] . '.tgl_buka', '<=', $data['tgl_kondisi']],
                                [$data['tb_simp'] . '.tgl_tutup', '>=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_simp . '.tgl_buka', 'ASC');
                },
                'simpanan.realSimpananTerbesar' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi'])->orderBy('id', 'desc');
                },
            ])
            ->orderBy('rek_simp', 'ASC')->get();

        $data['lunas'] = Simpanan::where([
            ['tgl_tutup', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->get();

        $view = view('pelaporan.view.simpanan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pemanfaat_aktif(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $tb_kel = 'kelompok_' . $data['kec']->id;
                    $data['tb_pinj'] = $tb_pinj;

                    $query->select(
                        $tb_pinj . '.*',
                        $tb_angg . '.namadepan',
                        $tb_angg . '.alamat',
                        $tb_angg . '.nik',
                        $tb_angg . '.kk',
                        $tb_kel . '.nama_kelompok',
                        'desa.nama_desa',
                        'desa.kd_desa',
                        'desa.kode_desa',
                        'sebutan_desa.sebutan_desa'
                    )
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj . '.nia')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinj . '.id_kel')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->where($tb_pinj . '.sistem_angsuran', '!=', '12')->where($tb_pinj . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj'] . '.status', 'A'],
                                [$data['tb_pinj'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj'] . '.status', 'L'],
                                [$data['tb_pinj'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj'] . '.status', 'R'],
                                [$data['tb_pinj'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj'] . '.status', 'H'],
                                [$data['tb_pinj'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj . '.tgl_cair', 'ASC');
                }
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.pemanfaat_aktif', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function proposal(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj'] = $tb_pinj;

                    $query->select($tb_pinj . '.*', $tb_ang . '.namadepan',  'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->where($tb_pinj . '.sistem_angsuran', '!=', '12')->where($tb_pinj . '.sistem_angsuran', '!=', '25')->where($tb_pinj . '.status', 'P')
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj . '.tgl_proposal', 'ASC');
                },
                'pinjaman_anggota.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.proposal', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function verifikasi(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj'] = $tb_pinj;

                    $query->select($tb_pinj . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->where($tb_pinj . '.sistem_angsuran', '!=', '12')->where($tb_pinj . '.sistem_angsuran', '!=', '25')->where($tb_pinj . '.status', 'V')
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj . '.tgl_verifikasi', 'ASC');
                },
                'pinjaman_anggota.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.verifikasi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function waiting(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj'] = $tb_pinj;

                    $query->select($tb_pinj . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->where($tb_pinj . '.sistem_angsuran', '!=', '12')->where($tb_pinj . '.sistem_angsuran', '!=', '25')->where($tb_pinj . '.status', 'W')
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj . '.tgl_tunggu', 'ASC');
                },
                'pinjaman_anggota.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.waiting', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    private function pinjaman_per_kelompok(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
                    $tb_kel = 'kelompok_' . $data['kec']->id;
                    $data['tb_pinkel'] = $tb_pinkel;

                    $query->select($tb_pinkel . '.*', $tb_kel . '.nama_kelompok', $tb_kel . '.ketua', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinkel . '.sistem_angsuran', '!=', '12')->where($tb_pinkel . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.tgl_cair', 'ASC');
                },
                'pinjaman_kelompok.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.angsuran_pokok',
                'pinjaman_kelompok.angsuran_jasa',
            ])->get();

        $data['lunas'] = PinjamanIndividu::where([
            ['tgl_lunas', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->with('saldo', 'target')->get();

        $view = view('pelaporan.view.perkembangan_piutang.lpp_kelompok', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pinjaman_individu(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'agent.agent AS nama_agent', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('agent', $tb_pinj_i . '.id_agent', '=', 'agent.id')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->whereNotIn($tb_pinj_i . '.sistem_angsuran', ['12', '25'])
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.id_agent', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $data['lunas'] = PinjamanIndividu::where([
            ['tgl_lunas', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->with('saldo', 'target')->get();

        $view = view('pelaporan.view.perkembangan_piutang.lpp_individu', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pinjaman_per_desa(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $data['lunas'] = PinjamanIndividu::where([
            ['tgl_lunas', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->with('saldo', 'target')->get();

        $view = view('pelaporan.view.perkembangan_piutang.lpp_desa', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function kolek_per_kelompok(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
                    $tb_kel = 'kelompok_' . $data['kec']->id;
                    $data['tb_pinkel'] = $tb_pinkel;

                    $query->select($tb_pinkel . '.*', $tb_kel . '.nama_kelompok', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinkel . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.tgl_cair', 'ASC');
                },
                'pinjaman_kelompok.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.kolek_kelompok', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    private function kolek_individu(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;

        // Default: Tahunan
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        // Jika hari terisi, maka harian
        if (!empty($hari) && $hari != '00' && $hari != '0') {
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
        }
        // Jika hanya bulan terisi (bulanan)
        elseif ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.kolek_individu', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function kolek_per_desa(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;

        // Default: Tahunan
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        // Jika hari terisi, maka harian
        if (!empty($hari) && $hari != '00' && $hari != '0') {
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
        }
        // Jika hanya bulan terisi (bulanan)
        elseif ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_anggota.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_anggota.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.kolek_desa', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function kredit_barang(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_individu' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_angg . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_individu.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_individu.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $data['lunas'] = PinjamanIndividu::where([
            ['tgl_lunas', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->with('saldo', 'target')->get();

        $view = view('pelaporan.view.perkembangan_piutang.kredit_barang', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function cadangan_penghapusan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select($tb_pinj_i . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj_i'] . '.status', 'A'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'L'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'R'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinj_i'] . '.status', 'H'],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
                },
                'pinjaman_anggota.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_anggota.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.cadangan_penghapusan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rencana_realisasi(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['tgl_cair'] = $thn . '-';
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl_cair'] = $thn . '-' . $bln . '-';
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_angg = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj_i'] = $tb_pinj_i;

                    $query->select(
                        $tb_pinj_i . '.*',
                        $tb_angg . '.namadepan',
                        $tb_angg . '.nik',
                        $tb_angg . '.alamat',
                        $tb_angg . '.tempat_lahir',
                        $tb_angg . '.tgl_lahir',
                        'desa.nama_desa',
                        'desa.kd_desa',
                        'desa.kode_desa',
                        'sebutan_desa.sebutan_desa'
                    )
                        ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                        ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->where($tb_pinj_i . '.sistem_angsuran', '!=', '12')->where($tb_pinj_i . '.sistem_angsuran', '!=', '25')
                        ->where(function ($query) use ($data) {
                            $query->where($data['tb_pinj_i'] . '.tgl_cair', 'LIKE', $data['tgl_cair'] . '%')
                                ->where(function ($query) use ($data) {
                                    $query->where([
                                        [$data['tb_pinj_i'] . '.status', 'A'],
                                        [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I']
                                    ])->orwhere([
                                        [$data['tb_pinj_i'] . '.status', 'L'],
                                        [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I']
                                    ])->orwhere([
                                        [$data['tb_pinj_i'] . '.status', 'R'],
                                        [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I']
                                    ])->orwhere([
                                        [$data['tb_pinj_i'] . '.status', 'H'],
                                        [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I']
                                    ]);
                                });
                        });
                },
                'pinjaman_anggota.sis_pokok'
            ])->get();

        $data['keuangan'] = $keuangan;
        $view = view('pelaporan.view.perkembangan_piutang.rencana_realisasi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rencana_realisasi_k(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        if (strlen($hari) > 0 && strlen($bln) > 0) {
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
        } elseif (strlen($bln) > 0) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        } else {
            $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tahun($tgl);
        }

        $triwulan = [
            '01' => ['1', '2', '3'],
            '02' => ['1', '2', '3'],
            '03' => ['1', '2', '3'],
            '04' => ['4', '5', '6'],
            '05' => ['4', '5', '6'],
            '06' => ['4', '5', '6'],
            '07' => ['7', '8', '9'],
            '08' => ['7', '8', '9'],
            '09' => ['7', '8', '9'],
            '10' => ['10', '11', '12'],
            '11' => ['10', '11', '12'],
            '12' => ['10', '11', '12'],
        ];

        $bulan_tampil = $triwulan[$data['bulan']];
        $bulan1 = str_pad($bulan_tampil[0], 2, '0', STR_PAD_LEFT);
        $bulan3 = str_pad($bulan_tampil[2], 2, '0', STR_PAD_LEFT);

        $tgl_awal = $data['tahun'] . '-' . $bulan1 . '-01';
        $tgl_akhir = date('Y-m-t', strtotime($data['tahun'] . '-' . $bulan3 . '-01'));
        $data['tgl_akhir'] = $tgl_akhir;

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
                    $tb_kel = 'kelompok_' . $data['kec']->id;
                    $data['tb_pinkel'] = $tb_pinkel;

                    $query->select(
                        $tb_pinkel . '.*',
                        $tb_kel . '.nama_kelompok',
                        $tb_kel . '.ketua',
                        'desa.nama_desa',
                        'desa.kd_desa',
                        'desa.kode_desa',
                        'sebutan_desa.sebutan_desa'
                    )
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->where($tb_pinkel . '.sistem_angsuran', '!=', '12')->where($tb_pinkel . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_akhir']]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.id', 'ASC');
                },
                'pinjaman_kelompok.real' => function ($query) use ($tgl_awal, $tgl_akhir) {
                    $query->whereBetween('tgl_transaksi', [$tgl_awal, $tgl_akhir]);
                },
                'pinjaman_kelompok.ra' => function ($query) use ($tgl_awal, $tgl_akhir) {
                    $query->whereBetween('jatuh_tempo', [$tgl_awal, $tgl_akhir]);
                }
            ])->get();

        $data['keuangan'] = $keuangan;
        $data['judul_halaman'] = 'Rencana Realisasi Kelompok';
        $view = view('pelaporan.view.perkembangan_piutang.rencana_realisasi_k', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function tagihan_hari_ini(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = $hari . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['pinjaman'] = PinjamanAnggota::where('status', 'A')->whereDay('tgl_cair', date('d', strtotime($tgl)))->with([
            'target' => function ($query) use ($tgl) {
                $query->where([
                    ['jatuh_tempo', $tgl],
                    ['angsuran_ke', '!=', '0']
                ]);
            },
            'saldo' => function ($query) use ($tgl) {
                $query->where('tgl_transaksi', '<=', $tgl);
            },
            'anggota',
            'anggota.d'
        ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.jatuh_tempo', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function menunggak(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = $hari . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->with([
                'pinjaman_anggota' => function ($query) use ($data) {
                    $tb_pinj = 'pinjaman_anggota_' . $data['kec']->id;
                    $tb_ang = 'anggota_' . $data['kec']->id;
                    $data['tb_pinj'] = $tb_pinj;

                    $query->select($tb_pinj . '.*', $tb_ang . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_ang, $tb_ang . '.id', '=', $tb_pinj . '.nia')
                        ->join('desa', $tb_ang . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real_i' => function ($query) use ($data) {
                            $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where($tb_pinj . '.sistem_angsuran', '!=', '12')->where($tb_pinj . '.sistem_angsuran', '!=', '25')->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinj'] . '.status', 'A'],
                                [$data['tb_pinj'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj'] . '.status', 'L'],
                                [$data['tb_pinj'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj'] . '.status', 'R'],
                                [$data['tb_pinj'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinj'] . '.status', 'H'],
                                [$data['tb_pinj'] . '.tgl_lunas', '>=', $data['tgl_kondisi']]
                            ]);
                        })
                        ->orderBy($tb_ang . '.desa', 'ASC')
                        ->orderBy($tb_pinj . '.nia', 'ASC')
                        ->orderBy($tb_pinj . '.tgl_cair', 'ASC');
                },
                'pinjaman_anggota.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_anggota.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_anggota.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.tunggakan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    private function ati(array $data)
    {
        $data['laporan'] = 'Aset Tetap dan Inventaris';
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['inventaris'] = Rekening::where('kode_akun', 'LIKE', '1.2.01%')
            ->with([
                'inventaris' => function ($query) use ($data) {
                    $query->where([
                        ['jenis', '1'],
                        ['status', '!=', '0'],
                        ['tgl_beli', '<=', $data['tgl_kondisi']],
                        ['tgl_beli', 'NOT LIKE', '']
                    ])->orderBy('tgl_beli', 'ASC');
                }
            ])
            ->get();

        $view = view('pelaporan.view.aset_tetap', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function atb(array $data)
    {
        $data['laporan'] = 'Aset Tak Berwujud';
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['inventaris'] = Rekening::where('kode_akun', 'LIKE', '1.2.03%')
            ->with([
                'inventaris' => function ($query) use ($data) {
                    $query->where([
                        ['jenis', '3'],
                        ['status', '!=', '0'],
                        ['tgl_beli', '<=', $data['tgl_kondisi']],
                        ['tgl_beli', 'NOT LIKE', '']
                    ])->orderBy('tgl_beli', 'ASC');
                }
            ])
            ->get();

        $view = view('pelaporan.view.aset_tak_berwujud', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function tingkat_kesehatan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['level', $data['kec']->ttd_mengetahui_lap],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['pengawas'] = User::where([
            ['level', '3'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bendahara'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $view = view('pelaporan.view.penilaian_kesehatan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function EB(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $title = [
            '1,2,3' => 'Januari - Maret',
            '4,5,6' => 'April - Juni',
            '7,8,9' => 'Juli - September',
            '10,11,12' => 'Oktober - Desember',
            '12' => 'Januari - Desember'
        ];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl'] = $title[$data['sub']] . ' ' . $thn;

        $bulan = explode(',', $data['sub']);
        $awal = $bulan[0];
        $akhir = end($bulan);

        $data['bulan_akhir'] = $awal - 1;
        $data['bulan_tampil'] = $bulan;
        $data['triwulan'] = array_search($data['sub'], array_keys($title)) + 1;

        $data['is_tahunan'] = ($data['sub'] === '12');

        $data['akun1'] = AkunLevel1::where('lev1', '>=', '4')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($data, $awal, $akhir) {
                $tahun = date('Y', strtotime($data['tgl_kondisi']));
                $query->where('tahun', $tahun)->orderBy('bulan', 'ASC')->orderBy('kode_akun', 'ASC');
            },
            'akun2.akun3.rek.kom_saldo.eb'
        ])->get();

        $data['keuangan'] = $keuangan;
        $view = view('pelaporan.view.e_budgeting', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pelunasan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $tb_pinkel = 'pinjaman_kelompok_' . Session::get('lokasi');
        $tb_kel = 'kelompok_' . Session::get('lokasi');
        $data['pinjaman_kelompok'] = PinjamanKelompok::select([
            $tb_pinkel . '.*',
            $tb_kel . '.nama_kelompok',
            $tb_kel . '.ketua',
            $tb_kel . '.alamat_kelompok',
            $tb_kel . '.telpon',
            'desa.nama_desa',
            'desa.kd_desa',
            'desa.kode_desa',
            'sebutan_desa.sebutan_desa',
            DB::raw('(TIMESTAMPDIFF(MONTH, DATE_ADD(' . $tb_pinkel . '.tgl_cair, INTERVAL ' . $tb_pinkel . '.jangka MONTH), CURRENT_DATE)) as sisa')
        ])->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
            ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
            ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
            ->withSum(['real' => function ($query) use ($data) {
                $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
            }], 'realisasi_pokok')
            ->withSum(['real' => function ($query) use ($data) {
                $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
            }], 'realisasi_jasa')
            ->where([
                [$tb_pinkel . '.sistem_angsuran', '!=', '12'],
                [$tb_pinkel . '.sistem_angsuran', '!=', '25'],
                [$tb_pinkel . '.status', 'A']
            ])
            ->whereRaw('(TIMESTAMPDIFF(MONTH, DATE_ADD(' . $tb_pinkel . '.tgl_cair, INTERVAL ' . $tb_pinkel . '.jangka MONTH), CURRENT_DATE)) BETWEEN -3 AND 0')
            ->with([
                'rencana1' => function ($query) use ($data, $tb_pinkel) {
                    $query->where('jatuh_tempo', '>=', $data['tahun'] . '-' . $data['bulan'] . '-01')->orWhere('jatuh_tempo', '<', $data['tahun'] . '-' . $data['bulan'] . '-01');
                }
            ])
            ->orderBy($tb_kel . '.desa', 'ASC')
            ->orderBy($tb_pinkel . '.id', 'ASC')->get();

        $view = view('pelaporan.view.perkembangan_piutang.pelunasan', $data)->render();
        $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

        $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
        return $pdf->stream();
    }

    private function alokasi_laba(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['transaksi'] = Transaksi::whereYear('tgl_transaksi', $thn)
            ->where(function ($query) {
                $query->where('rekening_debit', '!=', '0')
                    ->orWhere('rekening_kredit', '!=', '0');
            })
            ->where('rekening_debit',  '3.2.01.01')
            ->with('user', 'rek_debit', 'rek_kredit', 'angs', 'angs.rek_debit', 'angs.rek_kredit')
            ->orderBy('tgl_transaksi', 'ASC')
            ->orderBy('idt', 'ASC')
            ->get();

        $data['sub_judul'] = 'Tahun ' . ($thn - 1);
        $data['tgl'] = Tanggal::tglLatin($tgl);


        $data['laporan'] = 'Alokasi Laba';
        $view = view('pelaporan.view.tutup_buku.alokasi_laba', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function jurnal_tutup_buku(array $data)
    {
        $thn = $data['tahun'];
        $bln = 1;
        $hari = 1;

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['tanggal_kondisi'] = Tanggal::tglLatin(date('Y-m-d', strtotime($tgl)));
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['saldo'] = Saldo::where([
            ['tahun', $thn - 1],
            ['bulan', '13']
        ])->with('rek')->orderBy('kode_akun', 'ASC')->get();
        $data['rek'] = Rekening::where('kode_akun', '3.2.01.01')->first();

        $data['tgl_transaksi'] = $thn - 1 . '-12-31';
        $data['laporan'] = 'Jurnal Awal Tahun';
        $view = view('pelaporan.view.tutup_buku.jurnal', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function neraca_tutup_buku(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = 1;
        $hari = 1;

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['tanggal_kondisi'] = Tanggal::tglLatin(date('Y-m-d', strtotime($tgl)));
        $data['sub_judul'] = 'Tahun' . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.saldo' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['laporan'] = 'Neraca Awal Tahun';
        $view = view('pelaporan.view.tutup_buku.neraca', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function CALK_tutup_buku(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = 1;
        $hari = 1;

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['tanggal_kondisi'] = Tanggal::tglLatin(date('Y-m-d', strtotime($tgl)));
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['nama_tgl'] = 'Awal Tahun ' . $thn;
        $data['sub_judul'] = 'Awal Tahun ' . $thn;

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            },
            'akun2.akun3.rek.trx_kredit' => function ($query) use ($data) {
                $query->where('keterangan_transaksi', 'Like', '%tahun ' . $data['tahun'] - 1);
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['sekr'] = User::where([
            ['level', '1'],
            ['jabatan', '2'],
            ['lokasi', Session::get('lokasi')],
        ])->first();

        $data['bend'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')],
        ])->first();

        $data['pengawas'] = User::where([
            ['level', '3'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')],
        ])->first();

        $data['saldo_calk'] = Saldo::where([
            ['kode_akun', $data['kec']->kd_kec],
            ['tahun', $thn]
        ])->get();

        $data['laporan'] = 'CALK Awal Tahun';
        $view = view('pelaporan.view.tutup_buku.calk', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function laba_rugi_tutup_buku(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = 1;
        $hari = 1;
        $awal_tahun = $thn . '-01-01';

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['tanggal_kondisi'] = Tanggal::tglLatin(date('Y-m-d', strtotime($tgl)));
        $data['sub_judul'] = 'Awal Tahun ' . $thn;
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['bulan_lalu'] = date('Y-m-t', strtotime('-1 month', strtotime($thn . '-' . $bln . '-10')));
        $data['header_lalu'] = 'Bulan Lalu';
        $data['header_sekarang'] = 'Bulan Ini';

        $jenis = 'Tahunan';
        if ($data['bulanan']) {
            $jenis = 'Bulanan';
        }

        $pph = $keuangan->pph($tgl, $jenis);
        $laba_rugi = $keuangan->laporan_laba_rugi($tgl, $jenis);

        $data['pph'] = [
            'bulan_lalu' => $pph['bulan_lalu'],
            'sekarang' => $pph['bulan_ini']
        ];

        $data['pendapatan'] = $laba_rugi['pendapatan'];
        $data['beban'] = $laba_rugi['beban'];
        $data['pendapatanNOP'] = $laba_rugi['pendapatan_non_ops'];
        $data['bebanNOP'] = $laba_rugi['beban_non_ops'];

        $view = view('pelaporan.view.laba_rugi', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function mou()
    {
        $keuangan = new Keuangan;
        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten', 'desa', 'ttd')->first();
        $kab = $kec->kabupaten;

        $data['logo'] = $kec->logo;
        $data['nama_lembaga'] = $kec->nama_lembaga_sort;
        $data['nama_kecamatan'] = $kec->sebutan_kec . ' ' . $kec->nama_kec;

        if (Keuangan::startWith($kab->nama_kab, 'KOTA') || Keuangan::startWith($kab->nama_kab, 'KAB')) {
            $data['nama_kecamatan'] .= ' ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kabupaten'] = ucwords(strtolower($kab->nama_kab));
        } else {
            $data['nama_kecamatan'] .= ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kabupaten'] = ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
        }

        $jabatan = '1';
        $level = '1 ';
        if (Session::get('lokasi') == '207') {
            $jabatan = '1';
            $level = '2';
        }

        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['jabatan', $jabatan],
            ['level', $level]
        ])->first();

        $data['kec'] = $kec;
        $data['keu'] = $keuangan;

        $view = view('pelaporan.view.mou', $data)->render();

        $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';


        $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
        return $pdf->stream();
    }

    public function ts()
    {
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();

        $view = view('pelaporan.view.ts', $data)->render();
        $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : [0, 0, 595.28, 352];

        $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
        return $pdf->stream();
    }

    public function invoice(AdminInvoice $invoice)
    {
        $root_domain = explode('.', request()->getHost())[0];
        $allowed = ['master', 'laravel'];

        $data['inv'] = AdminInvoice::where('idv', $invoice->idv)->with('jp', 'trx', 'kec', 'kec.kabupaten')->first();

        if (!$data['inv']) {
            abort(404);
        }

        if (!in_array($root_domain, $allowed)) {
            if (Session::get('lokasi') != $data['inv']->lokasi) {
                abort(404);
            }
        }

        $view = view('pelaporan.view.invoice', $data)->render();
        $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

        $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
        return $pdf->stream();
    }


    private function rekap_neraca(array $data)
    {
        $keuangan = new Keuangan;
        $data['keuangan'] = $keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Per ' . date('t', strtotime($tgl)) . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
        ])->orderBy('kode_akun', 'ASC')->get();

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }
        $data['th'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['th'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['akun3'][$kec->id] = AkunLevel3::where('lev1', '<=', '3')->with([
                'rek',
                'rek.kom_saldo' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                },
            ])->orderBy('kode_akun')->get()->pluck([], 'kode_akun');

            $data['laba_rugi'][$kec->id] = $keuangan->laba_rugi($data['tgl_kondisi']);
        }

        $view = view('pelaporan.view.rekap_neraca', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function rekap_neraca2(array $data)
    {
        $keuangan = new Keuangan;
        $data['keuangan'] = $keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Per ' . date('t', strtotime($tgl)) . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['th'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['th'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
        ])->orderBy('kode_akun', 'ASC')->get();

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['akun3'][$kec->id] = AkunLevel3::where('lev1', '<=', '3')->with([
                'rek',
                'rek.kom_saldo' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                },
            ])->orderBy('kode_akun')->get()->pluck([], 'kode_akun');

            $data['laba_rugi'][$kec->id] = $keuangan->laba_rugi($data['tgl_kondisi']);
        }

        $view = view('pelaporan.view.rekap_neraca2', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_rb(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);
        $awal_tahun = $thn . '-01-01';

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Periode ' . Tanggal::tglLatin($thn . '-' . $bln . '-01') . ' S.D ' . Tanggal::tglLatin($data['tgl_kondisi']);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['bulan_lalu'] = date('Y-m-t', strtotime('-1 month', strtotime($thn . '-' . $bln . '-10')));
        $data['header_lalu'] = 'Bulan Lalu';
        $data['header_sekarang'] = 'Bulan Ini';
        $jenis = 'Bulanan';

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['laba_rugi'][$kec->id] = $keuangan->rekening_laba_rugi($data['tgl_kondisi']);
            $pph = $keuangan->pph($data['tgl_kondisi'], $jenis);
            $data['pph'][$kec->id] = [
                'bulan_lalu' => $pph['bulan_lalu'],
                'sekarang' => $pph['bulan_ini']
            ];
        }
        $data['laba_rugi'][0] = reset($data['laba_rugi']);

        $view = view('pelaporan.view.rekap_rb', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_rb2(array $data)
    {
        $keuangan = new Keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);
        $awal_tahun = $thn . '-01-01';

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Periode ' . Tanggal::tglLatin($thn . '-' . $bln . '-01') . ' S.D ' . Tanggal::tglLatin($data['tgl_kondisi']);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['bulan_lalu'] = date('Y-m-t', strtotime('-1 month', strtotime($thn . '-' . $bln . '-10')));
        $data['header_lalu'] = 'Bulan Lalu';
        $data['header_sekarang'] = 'Bulan Ini';
        $jenis = 'Bulanan';

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['laba_rugi'][$kec->id] = $keuangan->rekening_laba_rugi($data['tgl_kondisi']);
            $pph = $keuangan->pph($data['tgl_kondisi'], $jenis);
            $data['pph'][$kec->id] = [
                'bulan_lalu' => $pph['bulan_lalu'],
                'sekarang' => $pph['bulan_ini']
            ];
        }
        $data['laba_rugi'][0] = reset($data['laba_rugi']);

        $view = view('pelaporan.view.rekap_rb2', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_modal(array $data)
    {
        $keuangan = new Keuangan;
        $data['keuangan'] = $keuangan;

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = ($data['hari']);

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Per ' . date('t', strtotime($tgl)) . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['rekening'][$kec->id] = Rekening::where('lev1', '3')->where('lev2', '1')->with([
                'kom_saldo' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                }
            ])->orderBy('lev1')->orderBy('lev2')->orderBy('lev3', 'DESC')->orderBy('nama_akun')->get();

            $data['rekening2'][$kec->id] = Rekening::where('lev1', '3')->where('lev2', '2')->with([
                'kom_saldo' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                }
            ])->get();

            $data['laba_rugi'][$kec->id] = $keuangan->laba_rugi($data['tgl_kondisi']);
        }
        $data['laba_rugi'][0] = reset($data['laba_rugi']);
        $data['rekening'][0] = reset($data['rekening']);
        $data['rekening2'][0] = reset($data['rekening2']);

        $view = view('pelaporan.view.rekap_perubahan_modal', $data)->render();
        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_arus_kas_v1(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $tgl  = $thn . '-' . $bln . '-' . $hari;

        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl']       = Tanggal::tahun($tgl);
        $data['jenis']     = 'Tahunan';
        $data['awal']      = 'TAHUN';
        $tgl_lalu          = $thn . '-00-00';

        if ($data['bulanan'] && !($data['laporan'] == '1' || $data['laporan'] == '2')) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl']       = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['jenis']     = 'Bulanan';
            $data['awal']      = 'BULAN';
            $bulan_lalu        = $bln - 1;
            if ($bulan_lalu <= 0) {
                $bulan_lalu = 12;
                $thn -= 1;
            }
            $tgl_lalu = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu . '-01'));
        }

        if ($data['laporan'] == '1') {
            $data['laporan']   = 'Arus Kas Semester I';
            $data['sub_judul'] = 'Semester I Tahun ' . Tanggal::tahun($tgl);
            $data['jenis']     = 'Semester I';
            $data['tgl']       = Tanggal::tglLatin($thn . '-01-01') . ' S.D ' . Tanggal::tglLatin($thn . '-06-30');
        }

        if ($data['laporan'] == '2') {
            $data['laporan']   = 'Arus Kas Semester II';
            $data['sub_judul'] = 'Semester II Tahun ' . Tanggal::tahun($tgl);
            $data['jenis']     = 'Semester II';
            $data['tgl']       = Tanggal::tglLatin($thn . '-07-01') . ' S.D ' . Tanggal::tglLatin($thn . '-12-31');
        }

        $daftarLokasi = [];
        foreach (explode(',', Session::get('rekapan')) as $l) {
            $daftarLokasi[] = trim($l);
        }

        Session::put('lokasi', $daftarLokasi[0]);
        $strukturArusKas = ArusKas::where('sub', '0')->with('child')->orderBy('id', 'ASC')->get();

        $kecamatan     = DB::table('kecamatan')->whereIn('id', $daftarLokasi)->get()->keyBy('id');
        $dataPerlokasi = [];

        foreach ($daftarLokasi as $lokasiId) {
            Session::put('lokasi', $lokasiId);

            $saldoBulanLalu = $keuangan->saldoKas($tgl_lalu);
            $childValues    = [];

            foreach ($strukturArusKas as $ak) {
                foreach ($ak->child as $child) {
                    $childValues[$child->rekening] = $keuangan->arus_kas($child->rekening, $data['tgl_kondisi'] ?? $tgl, $data['jenis']);
                }
            }

            $dataPerlokasi[$lokasiId] = [
                'saldo_bulan_lalu' => $saldoBulanLalu,
                'child_values'     => $childValues,
            ];
        }

        $totalChildValues    = [];
        $totalSaldoBulanLalu = 0;

        foreach ($dataPerlokasi as $lokasiData) {
            $totalSaldoBulanLalu += $lokasiData['saldo_bulan_lalu'];
            foreach ($lokasiData['child_values'] as $rekening => $nilai) {
                $totalChildValues[$rekening] = ($totalChildValues[$rekening] ?? 0) + $nilai;
            }
        }

        $data['keuangan']         = $keuangan;
        $data['arus_kas']         = $strukturArusKas;
        $data['saldo_bulan_lalu'] = $totalSaldoBulanLalu;
        $data['total_child']      = $totalChildValues;
        $data['kecamatan_list']   = $kecamatan;
        $data['lokasi_list']      = $daftarLokasi;
        $data['data_perlokasi']   = $dataPerlokasi;

        Session::put('lokasi', $daftarLokasi[0]);
        $data['kec'] = DB::table('kecamatan')->where('id', $daftarLokasi[0])->first();

        $view = view('pelaporan.view.rekap_arus_kas_v1', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view)->setPaper('A4', 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_arus_kas_v2(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $tgl  = $thn . '-' . $bln . '-' . $hari;

        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl']       = Tanggal::tahun($tgl);
        $data['jenis']     = 'Tahunan';
        $data['awal']      = 'TAHUN';
        $tgl_lalu          = $thn . '-00-00';

        if ($data['bulanan'] && !($data['laporan'] == '1' || $data['laporan'] == '2')) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl']       = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['jenis']     = 'Bulanan';
            $data['awal']      = 'BULAN';
            $bulan_lalu        = $bln - 1;
            if ($bulan_lalu <= 0) {
                $bulan_lalu = 12;
                $thn -= 1;
            }
            $tgl_lalu = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu . '-01'));
        }

        if ($data['laporan'] == '1') {
            $data['laporan']   = 'Arus Kas Semester I';
            $data['sub_judul'] = 'Semester I Tahun ' . Tanggal::tahun($tgl);
            $data['jenis']     = 'Semester I';
            $data['tgl']       = Tanggal::tglLatin($thn . '-01-01') . ' S.D ' . Tanggal::tglLatin($thn . '-06-30');
        }

        if ($data['laporan'] == '2') {
            $data['laporan']   = 'Arus Kas Semester II';
            $data['sub_judul'] = 'Semester II Tahun ' . Tanggal::tahun($tgl);
            $data['jenis']     = 'Semester II';
            $data['tgl']       = Tanggal::tglLatin($thn . '-07-01') . ' S.D ' . Tanggal::tglLatin($thn . '-12-31');
        }

        $daftarLokasi = [];
        foreach (explode(',', Session::get('rekapan')) as $l) {
            $daftarLokasi[] = trim($l);
        }

        Session::put('lokasi', $daftarLokasi[0]);
        $strukturArusKas = ArusKas::where('sub', '0')->with('child')->orderBy('id', 'ASC')->get();

        $dataPerlokasi = [];

        foreach ($daftarLokasi as $lokasiId) {
            Session::put('lokasi', $lokasiId);

            $saldoBulanLalu = $keuangan->saldoKas($tgl_lalu);
            $childValues    = [];

            foreach ($strukturArusKas as $ak) {
                foreach ($ak->child as $child) {
                    $childValues[$child->rekening] = $keuangan->arus_kas($child->rekening, $data['tgl_kondisi'] ?? $tgl, $data['jenis']);
                }
            }

            $dataPerlokasi[$lokasiId] = [
                'saldo_bulan_lalu' => $saldoBulanLalu,
                'child_values'     => $childValues,
            ];
        }

        $totalChildValues    = [];
        $totalSaldoBulanLalu = 0;

        foreach ($dataPerlokasi as $lokasiData) {
            $totalSaldoBulanLalu += $lokasiData['saldo_bulan_lalu'];
            foreach ($lokasiData['child_values'] as $rekening => $nilai) {
                $totalChildValues[$rekening] = ($totalChildValues[$rekening] ?? 0) + $nilai;
            }
        }

        $data['keuangan']         = $keuangan;
        $data['arus_kas']         = $strukturArusKas;
        $data['saldo_bulan_lalu'] = $totalSaldoBulanLalu;
        $data['total_child']      = $totalChildValues;
        $data['lokasi_list']      = $daftarLokasi;
        $data['data_perlokasi']   = $dataPerlokasi;

        Session::put('lokasi', $daftarLokasi[0]);
        $data['kec'] = DB::table('kecamatan')->where('id', $daftarLokasi[0])->first();

        $view = view('pelaporan.view.rekap_arus_kas_v2', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view)->setPaper('A4', 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_calk(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['nama_tgl'] = 'Tahun ' . $thn;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['th'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['nama_tgl'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' Tahun ' . $thn;
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['th'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
        ])->orderBy('kode_akun', 'ASC')->get();

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['akun3'][$kec->id] = AkunLevel3::where('lev1', '<=', '3')->with([
                'rek',
                'rek.kom_saldo' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                },
            ])->orderBy('kode_akun')->get()->pluck([], 'kode_akun');

            $data['laba_rugi'][$kec->id] = $keuangan->laba_rugi($data['tgl_kondisi']);
        }

        $rekening = [];
        foreach ($kecamatan as $kec) {
            foreach ($data['akun3'][$kec->id] as $akun3) {
                foreach ($akun3->rek as $rek) {
                    $lev1 = $rek->lev1;
                    $lev2 = $rek->lev2;
                    $lev3 = str_pad($rek->lev3, 2, '0', STR_PAD_LEFT);
                    $kode_akun3 = $lev1 . '.' . $lev2 . '.' . $lev3 . '.00';

                    $nama_akun = $rek->kode_akun . '||' . $rek->nama_akun;
                    $rekening[$kode_akun3][$nama_akun][$kec->id] = $rek->kom_saldo;
                }
            }
        }

        $data['rekening'] = $rekening;
        $view = view('pelaporan.view.rekap_calk', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function rekap_calk2(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['nama_tgl'] = 'Tahun ' . $thn;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['th'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['nama_tgl'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' Tahun ' . $thn;
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['th'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['debit'] = 0;
        $data['kredit'] = 0;

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
        ])->orderBy('kode_akun', 'ASC')->get();

        $Lokasi = [];
        $daftarLokasi = explode(',', Session::get('rekapan'));
        foreach ($daftarLokasi as $lokasi) {
            $Lokasi[] = trim($lokasi);
        }

        $kecamatan = DB::table('kecamatan')->whereIn('id', $Lokasi)->get();
        foreach ($kecamatan as $kec) {
            $data['kecamatan'][$kec->id] = $kec;
            Session::put('lokasi', $kec->id);

            $data['akun3'][$kec->id] = AkunLevel3::where('lev1', '<=', '3')->with([
                'rek',
                'rek.kom_saldo' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                        $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                    });
                },
            ])->orderBy('kode_akun')->get()->pluck([], 'kode_akun');

            $data['laba_rugi'][$kec->id] = $keuangan->laba_rugi($data['tgl_kondisi']);
        }

        $rekening = [];
        foreach ($kecamatan as $kec) {
            foreach ($data['akun3'][$kec->id] as $akun3) {
                foreach ($akun3->rek as $rek) {
                    $lev1 = $rek->lev1;
                    $lev2 = $rek->lev2;
                    $lev3 = str_pad($rek->lev3, 2, '0', STR_PAD_LEFT);
                    $kode_akun3 = $lev1 . '.' . $lev2 . '.' . $lev3 . '.00';

                    $nama_akun = $rek->kode_akun . '||' . $rek->nama_akun;
                    $rekening[$kode_akun3][$nama_akun][$kec->id] = $rek->kom_saldo;
                }
            }
        }

        $data['rekening'] = $rekening;
        $view = view('pelaporan.view.rekap_calk2', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    //mingguan
    private function pinjaman_individu_mingguan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) use ($kec) {
            $query->where('lokasi', '0')
                ->orWhere(function ($query) use ($kec) {
                    $query->where('kecuali', 'NOT LIKE', "%-{$kec['id']}-%")
                        ->where('lokasi', 'LIKE', "%-{$kec['id']}-%");
                });
        })->with([
            'pinjaman_individu' => function ($query) use ($data) {
                $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                $tb_angg = 'anggota_' . $data['kec']->id;
                $data['tb_pinj_i'] = $tb_pinj_i;

                $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                    ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                    ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                    ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                    ->withSum(['real_i' => function ($query) use ($data) {
                        $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                    }], 'realisasi_pokok')
                    ->withSum(['real_i' => function ($query) use ($data) {
                        $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                    }], 'realisasi_jasa')
                    ->whereIn($tb_pinj_i . '.sistem_angsuran', ['12', '25'])->where(function ($query) use ($data) {
                        $query->where([
                            [$data['tb_pinj_i'] . '.status', 'A'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'L'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'L'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'R'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'R'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'H'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'H'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ]);
                    })
                    ->orderBy($tb_angg . '.desa', 'ASC')
                    ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
            },
            'pinjaman_individu.saldo' => function ($query) use ($data) {
                $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
            },
            'pinjaman_individu.target' => function ($query) use ($data) {
                $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
            },
            'pinjaman_individu.angsuran_pokok'
        ])->get();

        $data['lunas'] = PinjamanIndividu::where([
            ['tgl_lunas', '<', $thn . '-01-01'],
            ['status', 'L']
        ])->with('saldo', 'target')->get();
        foreach ($data['jenis_pp'] as $jpp) {
            $jpp->nama_jpp = $jpp->nama_jpp . ' Mingguan';
        }
        $view = view('pelaporan.view.perkembangan_piutang.lpp_individu', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    private function kolek_individu_mingguan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['tgl_lalu'] = $data['tahun'] . '-' . $data['bulan'] . '-01';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) use ($kec) {
            $query->where('lokasi', '0')
                ->orWhere(function ($query) use ($kec) {
                    $query->where('kecuali', 'NOT LIKE', "%-{$kec['id']}-%")
                        ->where('lokasi', 'LIKE', "%-{$kec['id']}-%");
                });
        })->with([
            'pinjaman_individu' => function ($query) use ($data) {
                $tb_pinj_i = 'pinjaman_anggota_' . $data['kec']->id;
                $tb_angg = 'anggota_' . $data['kec']->id;
                $data['tb_pinj_i'] = $tb_pinj_i;

                $query->select($tb_pinj_i . '.*', $tb_angg . '.namadepan', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                    ->join($tb_angg, $tb_angg . '.id', '=', $tb_pinj_i . '.nia')
                    ->join('desa', $tb_angg . '.desa', '=', 'desa.kd_desa')
                    ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                    ->withSum(['real_i' => function ($query) use ($data) {
                        $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                    }], 'realisasi_pokok')
                    ->withSum(['real_i' => function ($query) use ($data) {
                        $query->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                    }], 'realisasi_jasa')
                    ->whereIn($tb_pinj_i . '.sistem_angsuran', ['12', '25'])->where(function ($query) use ($data) {
                        $query->where([
                            [$data['tb_pinj_i'] . '.status', 'A'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'L'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'L'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'R'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'R'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'H'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ])->orwhere([
                            [$data['tb_pinj_i'] . '.status', 'H'],
                            [$data['tb_pinj_i'] . '.jenis_pinjaman', 'I'],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                            [$data['tb_pinj_i'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                        ]);
                    })
                    ->orderBy($tb_angg . '.desa', 'ASC')
                    ->orderBy($tb_pinj_i . '.tgl_cair', 'ASC');
            },
            'pinjaman_individu.saldo' => function ($query) use ($data) {
                $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
            },
            'pinjaman_individu.target' => function ($query) use ($data) {
                $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
            }
        ])->get();
        foreach ($data['jenis_pp'] as $jpp) {
            $jpp->nama_jpp = $jpp->nama_jpp . ' Mingguan';
        }
        $view = view('pelaporan.view.perkembangan_piutang.kolek_individu', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pinjaman_kelompok_spp(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $tb_pinkel_exists = \Schema::hasTable('pinjaman_kelompok_' . $data['kec']->id);
        $tb_kel_exists = \Schema::hasTable('kelompok_' . $data['kec']->id);

        if (!$tb_pinkel_exists || !$tb_kel_exists) {
            $view = view('pelaporan.view.perkembangan_piutang._kosong', [
                'laporan' => 'Perkembangan Pinjaman Kelompok',
                'sub_judul' => $data['sub_judul'],
                'tgl' => $data['tgl'] ?? '',
                'type' => $data['type'] ?? 'pdf',
                'pesan' => 'Lokasi ini (' . $data['kec']->nama_kec . ') tidak memiliki tabel pinjaman kelompok.',
            ])->render();
            return $data['type'] == 'pdf' ? \PDF::loadHTML($view)->setPaper('A4', 'landscape')->stream() : $view;
        }

        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->orWhereIn('id', function ($sub) use ($data) {
                $tb = 'pinjaman_kelompok_' . $data['kec']->id;
                $sub->from($tb)->select('jenis_pp')->where('tgl_cair', '<=', $data['tgl_kondisi']);
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
                    $tb_kel = 'kelompok_' . $data['kec']->id;
                    $data['tb_pinkel'] = $tb_pinkel;

                    // Pre-compute jenis_pp mana saja yang punya pinkel sistem mingguan (12/25) di lokasi ini
                    $mingguan_jpp_ids = \DB::table($tb_pinkel)
                        ->whereIn('sistem_angsuran', ['12', '25'])
                        ->distinct()
                        ->pluck('jenis_pp')
                        ->toArray();

                    $query->select($tb_pinkel . '.*', $tb_kel . '.nama_kelompok', $tb_kel . '.ketua', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real' => function ($q) use ($data) {
                            $q->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real' => function ($q) use ($data) {
                            $q->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->where(function ($q) use ($tb_pinkel, $mingguan_jpp_ids) {
                            if (!empty($mingguan_jpp_ids)) {
                                $q->whereIn($tb_pinkel . '.jenis_pp', $mingguan_jpp_ids)
                                    ->orWhereNotIn($tb_pinkel . '.sistem_angsuran', ['12', '25']);
                            } else {
                                $q->whereNotIn($tb_pinkel . '.sistem_angsuran', ['12', '25']);
                            }
                        })
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.tgl_cair', 'ASC');
                },
                'pinjaman_kelompok.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.sis_pokok'
            ])->get();

        $view = view('pelaporan.view.perkembangan_piutang.lpp_kelompok_spp', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function pinjaman_kelompok_mingguan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $tb_pinkel_exists = \Schema::hasTable('pinjaman_kelompok_' . $data['kec']->id);
        $tb_kel_exists = \Schema::hasTable('kelompok_' . $data['kec']->id);

        if (!$tb_pinkel_exists || !$tb_kel_exists) {
            $view = view('pelaporan.view.perkembangan_piutang._kosong', [
                'laporan' => 'Perkembangan Pinjaman Kelompok Mingguan',
                'sub_judul' => $data['sub_judul'],
                'tgl' => $data['tgl'] ?? '',
                'type' => $data['type'] ?? 'pdf',
                'pesan' => 'Lokasi ini (' . $data['kec']->nama_kec . ') tidak memiliki tabel pinjaman kelompok.',
            ])->render();
            return $data['type'] == 'pdf' ? \PDF::loadHTML($view)->setPaper('A4', 'landscape')->stream() : $view;
        }

        $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
        $tb_kel = 'kelompok_' . $data['kec']->id;
        $data['tb_pinkel'] = $tb_pinkel;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->orWhereIn('id', function ($sub) use ($data) {
                $tb = 'pinjaman_kelompok_' . $data['kec']->id;
                $sub->from($tb)->select('jenis_pp')->where('tgl_cair', '<=', $data['tgl_kondisi']);
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = $data['tb_pinkel'];
                    $tb_kel = 'kelompok_' . $data['kec']->id;

                    $query->select($tb_pinkel . '.*', $tb_kel . '.nama_kelompok', $tb_kel . '.ketua', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real' => function ($q) use ($data) {
                            $q->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real' => function ($q) use ($data) {
                            $q->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->whereIn($tb_pinkel . '.sistem_angsuran', ['12', '25'])
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.tgl_cair', 'ASC');
                },
                'pinjaman_kelompok.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.sis_pokok'
            ])->get();

        foreach ($data['jenis_pp'] as $jpp) {
            if (!str_contains($jpp->nama_jpp, 'Mingguan')) {
                $jpp->nama_jpp = $jpp->nama_jpp . ' Mingguan';
            }
        }

        $view = view('pelaporan.view.perkembangan_piutang.lpp_kelompok_mingguan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function kolek_kelompok_mingguan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $tb_pinkel_exists = \Schema::hasTable('pinjaman_kelompok_' . $data['kec']->id);
        $tb_kel_exists = \Schema::hasTable('kelompok_' . $data['kec']->id);

        if (!$tb_pinkel_exists || !$tb_kel_exists) {
            $view = view('pelaporan.view.perkembangan_piutang._kosong', [
                'laporan' => 'Kolektibilitas Kelompok Mingguan',
                'sub_judul' => $data['sub_judul'],
                'tgl' => $data['tgl'] ?? '',
                'type' => $data['type'] ?? 'pdf',
                'pesan' => 'Lokasi ini (' . $data['kec']->nama_kec . ') tidak memiliki tabel pinjaman kelompok.',
            ])->render();
            return $data['type'] == 'pdf' ? \PDF::loadHTML($view)->setPaper('A4', 'landscape')->stream() : $view;
        }

        $tb_pinkel = 'pinjaman_kelompok_' . $data['kec']->id;
        $tb_kel = 'kelompok_' . $data['kec']->id;
        $data['tb_pinkel'] = $tb_pinkel;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['jenis_pp'] = JenisProdukPinjaman::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
        })
            ->orWhere(function ($query) {
                $query->where('lokasi', session('lokasi'))
                    ->where('kecuali', 'NOT LIKE', '%#' . session('lokasi') . '#%');
            })
            ->orWhereIn('id', function ($sub) use ($data) {
                $tb = 'pinjaman_kelompok_' . $data['kec']->id;
                $sub->from($tb)->select('jenis_pp')->where('tgl_cair', '<=', $data['tgl_kondisi']);
            })
            ->with([
                'pinjaman_kelompok' => function ($query) use ($data) {
                    $tb_pinkel = $data['tb_pinkel'];
                    $tb_kel = 'kelompok_' . $data['kec']->id;

                    $query->select($tb_pinkel . '.*', $tb_kel . '.nama_kelompok', $tb_kel . '.ketua', 'desa.nama_desa', 'desa.kd_desa', 'desa.kode_desa', 'sebutan_desa.sebutan_desa')
                        ->join($tb_kel, $tb_kel . '.id', '=', $tb_pinkel . '.id_kel')
                        ->join('desa', $tb_kel . '.desa', '=', 'desa.kd_desa')
                        ->join('sebutan_desa', 'sebutan_desa.id', '=', 'desa.sebutan')
                        ->withSum(['real' => function ($q) use ($data) {
                            $q->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_pokok')
                        ->withSum(['real' => function ($q) use ($data) {
                            $q->where('tgl_transaksi', 'LIKE', '%' . $data['tahun'] . '-' . $data['bulan'] . '-%');
                        }], 'realisasi_jasa')
                        ->whereIn($tb_pinkel . '.sistem_angsuran', ['12', '25'])
                        ->where(function ($query) use ($data) {
                            $query->where([
                                [$data['tb_pinkel'] . '.status', 'A'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'L'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'R'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_cair', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ])->orwhere([
                                [$data['tb_pinkel'] . '.status', 'H'],
                                [$data['tb_pinkel'] . '.tgl_lunas', '<=', $data['tgl_kondisi']],
                                [$data['tb_pinkel'] . '.tgl_lunas', '>=', "$data[tahun]-01-01"]
                            ]);
                        })
                        ->orderBy($tb_kel . '.desa', 'ASC')
                        ->orderBy($tb_pinkel . '.tgl_cair', 'ASC');
                },
                'pinjaman_kelompok.saldo' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
                'pinjaman_kelompok.target' => function ($query) use ($data) {
                    $query->where('jatuh_tempo', '<=', $data['tgl_kondisi']);
                }
            ])->get();

        foreach ($data['jenis_pp'] as $jpp) {
            if (!str_contains($jpp->nama_jpp, 'Mingguan')) {
                $jpp->nama_jpp = $jpp->nama_jpp . ' Mingguan';
            }
        }

        $view = view('pelaporan.view.perkembangan_piutang.kolek_kelompok_mingguan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'landscape');
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    private function PTK_POJK(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Periode ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $kec = $data['kec'];

        $aset = $keuangan->aset($tgl);
        $total_aset = $aset['aset_produktif'];
        $cadangan_piutang_terbentuk = $aset['cadangan_piutang'];

        $bln_loop = str_pad((int) $bln, 2, '0', STR_PAD_LEFT);
        $rek_aset = Rekening::where('lev1', '1')->with([
            'kom_saldo' => function ($query) use ($thn, $bln_loop) {
                $query->where('tahun', $thn)->where(function ($query) use ($bln_loop) {
                    $query->where('bulan', '0')->orwhere('bulan', $bln_loop);
                });
            }
        ])->get();
        $sum_kas = 0;
        foreach ($rek_aset as $rek) {
            if ($rek->lev1 == '1' && $rek->lev2 == '1' && in_array($rek->lev3, ['01', '02'])) {
                $sum_kas += $keuangan->komSaldo($rek);
            }
        }

        $rek_liab = Rekening::where('lev1', '2')->with([
            'kom_saldo' => function ($query) use ($thn, $bln_loop) {
                $query->where('tahun', $thn)->where(function ($query) use ($bln_loop) {
                    $query->where('bulan', '0')->orwhere('bulan', $bln_loop);
                });
            }
        ])->get();
        $total_liabilitas = 0;
        $liabilitas_lancar = 0;
        foreach ($rek_liab as $rek) {
            $saldo = $keuangan->komSaldo($rek);
            $total_liabilitas += $saldo;
            if ($rek->lev2 == '1') {
                $liabilitas_lancar += $saldo;
            }
        }

        $rek_ekuitas = Rekening::where('lev1', '3')->with([
            'kom_saldo' => function ($query) use ($thn, $bln_loop) {
                $query->where('tahun', $thn)->where(function ($query) use ($bln_loop) {
                    $query->where('bulan', '0')->orwhere('bulan', $bln_loop);
                });
            }
        ])->get();
        $total_ekuitas = 0;
        foreach ($rek_ekuitas as $rek) {
            $total_ekuitas += $keuangan->komSaldo($rek);
        }

        $modal_disetor = $keuangan->modal_awal($tgl);

        $tk = $keuangan->tingkat_kesehatan($tgl);
        $outstanding_pinjaman = $tk['saldo_pokok'];

        $kolek_items = $tk['kolek_items'];
        $sum_kolek_total = $tk['sum_kolek_total'];

        $npl_neto = 0;
        $ppap_wajib_minimum = 0;
        foreach ($kolek_items as $idx => $item) {
            $nama = strtolower($item['nama'] ?? '');
            $saldo = $sum_kolek_total[$idx] ?? 0;
            $prosentase = (float) ($item['prosentase'] ?? 0);

            $is_lancar = str_contains($nama, 'lancar') && !str_contains($nama, 'kurang');
            $is_kurang_lancar = str_contains($nama, 'kurang');
            $is_diragukan = str_contains($nama, 'ragu');
            $is_macet = str_contains($nama, 'macet');
            $is_dpk = str_contains($nama, 'dpk') || str_contains($nama, 'dalam perhatian khusus');

            if ($is_kurang_lancar || $is_diragukan || $is_macet) {
                $npl_neto += $saldo;
            }

            if ($is_dpk) {
                $ppap_wajib_minimum += $saldo * 0.05;
            } elseif ($is_kurang_lancar) {
                $ppap_wajib_minimum += $saldo * 0.15;
            } elseif ($is_diragukan) {
                $ppap_wajib_minimum += $saldo * 0.50;
            } elseif ($is_macet) {
                $ppap_wajib_minimum += $saldo * 1.00;
            }
        }

        $rasio_solvabilitas = ($total_liabilitas > 0) ? ($total_aset / $total_liabilitas) * 100 : 0;
        $rasio_ekuitas = ($modal_disetor > 0) ? ($total_ekuitas / $modal_disetor) * 100 : 0;
        $rasio_npl_neto = ($outstanding_pinjaman > 0) ? ($npl_neto / $outstanding_pinjaman) * 100 : 0;
        $rasio_likuiditas = ($liabilitas_lancar > 0) ? ($sum_kas / $liabilitas_lancar) * 100 : 0;
        $ppap_coverage = ($ppap_wajib_minimum > 0) ? ($cadangan_piutang_terbentuk / $ppap_wajib_minimum) * 100 : 100;

        $pendapatan = $keuangan->pendapatan($tgl);
        $biaya = $keuangan->biaya($tgl);
        $laba_bersih = $pendapatan - $biaya;
        $roa = ($total_aset > 0) ? ($laba_bersih / $total_aset) * 100 : 0;

        $skor_permodalan = 0;
        $pm_rasio_sol = 0;
        if ($rasio_solvabilitas >= 120) {
            $pm_rasio_sol = 100;
        } elseif ($rasio_solvabilitas >= 115) {
            $pm_rasio_sol = 75;
        } elseif ($rasio_solvabilitas >= 110) {
            $pm_rasio_sol = 50;
        } elseif ($rasio_solvabilitas >= 105) {
            $pm_rasio_sol = 25;
        } else {
            $pm_rasio_sol = 0;
        }

        $pm_rasio_ek = 0;
        if ($rasio_ekuitas >= 100) {
            $pm_rasio_ek = 100;
        } elseif ($rasio_ekuitas >= 90) {
            $pm_rasio_ek = 75;
        } elseif ($rasio_ekuitas >= 75) {
            $pm_rasio_ek = 50;
        } elseif ($rasio_ekuitas >= 60) {
            $pm_rasio_ek = 25;
        } else {
            $pm_rasio_ek = 0;
        }
        $skor_permodalan = ($pm_rasio_sol * 0.5) + ($pm_rasio_ek * 0.5);

        $skor_kualitas_aset = 0;
        $ka_npl = 0;
        if ($rasio_npl_neto <= 2) {
            $ka_npl = 100;
        } elseif ($rasio_npl_neto <= 3.5) {
            $ka_npl = 75;
        } elseif ($rasio_npl_neto <= 5) {
            $ka_npl = 50;
        } elseif ($rasio_npl_neto <= 10) {
            $ka_npl = 25;
        } else {
            $ka_npl = 0;
        }

        $ka_ppap = 0;
        if ($ppap_coverage >= 100) {
            $ka_ppap = 100;
        } elseif ($ppap_coverage >= 80) {
            $ka_ppap = 75;
        } elseif ($ppap_coverage >= 60) {
            $ka_ppap = 50;
        } elseif ($ppap_coverage >= 40) {
            $ka_ppap = 25;
        } else {
            $ka_ppap = 0;
        }
        $skor_kualitas_aset = ($ka_npl * 0.6) + ($ka_ppap * 0.4);

        $skor_manajemen = 75;

        $skor_rentabilitas = 0;
        if ($roa >= 2.5) {
            $skor_rentabilitas = 100;
        } elseif ($roa >= 1.5) {
            $skor_rentabilitas = 75;
        } elseif ($roa >= 0.5) {
            $skor_rentabilitas = 50;
        } elseif ($roa >= 0) {
            $skor_rentabilitas = 25;
        } else {
            $skor_rentabilitas = 0;
        }

        $skor_likuiditas = 0;
        if ($rasio_likuiditas >= 10) {
            $skor_likuiditas = 100;
        } elseif ($rasio_likuiditas >= 7) {
            $skor_likuiditas = 75;
        } elseif ($rasio_likuiditas >= 4) {
            $skor_likuiditas = 50;
        } elseif ($rasio_likuiditas >= 2) {
            $skor_likuiditas = 25;
        } else {
            $skor_likuiditas = 0;
        }

        $skor_komposit = ($skor_permodalan * 0.25)
            + ($skor_kualitas_aset * 0.35)
            + ($skor_manajemen * 0.20)
            + ($skor_rentabilitas * 0.10)
            + ($skor_likuiditas * 0.10);

        $pk = 3;
        $pk_label = 'Cukup Sehat';
        if ($rasio_npl_neto >= 25 || $rasio_ekuitas < 50 || $ppap_coverage < 50) {
            $pk = 5;
            $pk_label = 'Tidak Sehat';
        } elseif ($skor_komposit >= 81) {
            $pk = 1;
            $pk_label = 'Sangat Sehat';
        } elseif ($skor_komposit >= 66) {
            $pk = 2;
            $pk_label = 'Sehat';
        } elseif ($skor_komposit >= 51) {
            $pk = 3;
            $pk_label = 'Cukup Sehat';
        } else {
            $pk = 4;
            $pk_label = 'Kurang Sehat';
        }

        $status_pengawasan = 'Normal';
        $status_pengawasan_label = 'Pengawasan Normal';
        $status_pengawasan_warna = '#28a745';
        $status_pengawasan_alasan = 'Seluruh rasio utama terpenuhi sesuai ketentuan POJK.';
        if ($pk == 5 || $rasio_ekuitas < 50 || $rasio_npl_neto >= 25) {
            $status_pengawasan = 'Khusus';
            $status_pengawasan_label = 'Pengawasan Khusus';
            $status_pengawasan_warna = '#dc3545';
            $alasan = [];
            if ($pk == 5) $alasan[] = 'Peringkat Komposit (PK 5)';
            if ($rasio_ekuitas < 50) $alasan[] = 'Rasio Ekuitas < 50% (' . number_format($rasio_ekuitas, 2) . '%)';
            if ($rasio_npl_neto >= 25) $alasan[] = 'NPL Neto >= 25% (' . number_format($rasio_npl_neto, 2) . '%)';
            $status_pengawasan_alasan = 'Dipicu oleh: ' . implode(', ', $alasan) . '.';
        } elseif ($pk == 4 || ($rasio_ekuitas >= 50 && $rasio_ekuitas < 75) || ($rasio_npl_neto > 5 && $rasio_npl_neto < 25)) {
            $status_pengawasan = 'Intensif';
            $status_pengawasan_label = 'Pengawasan Intensif';
            $status_pengawasan_warna = '#fd7e14';
            $alasan = [];
            if ($pk == 4) $alasan[] = 'Peringkat Komposit (PK 4)';
            if ($rasio_ekuitas >= 50 && $rasio_ekuitas < 75) $alasan[] = 'Rasio Ekuitas 50% s.d <75% (' . number_format($rasio_ekuitas, 2) . '%)';
            if ($rasio_npl_neto > 5 && $rasio_npl_neto < 25) $alasan[] = 'NPL Neto >5% s.d <25% (' . number_format($rasio_npl_neto, 2) . '%)';
            $status_pengawasan_alasan = 'Dipicu oleh: ' . implode(', ', $alasan) . '.';
        }

        $rekomendasi = [];

        // Helper format rupiah konsisten
        $rp = function ($n) {
            return 'Rp ' . number_format((float) $n, 0, ',', '.');
        };

        // ===== Hitung selisih/need untuk rekomendasi actionable =====
        // 1. Solvabilitas: butuh total_aset >= 1.10 * total_liabilitas
        //    cara paling cepat: turunkan liabilitas (bayar utang) atau naikkan ekuitas
        $need_solvabilitas = max(0, ($total_liabilitas * 1.10) - $total_aset);
        $target_ekuitas_solv = max(0, ($total_liabilitas * 1.10) - ($total_aset - $total_liabilitas));

        // 2. Ekuitas: butuh total_ekuitas >= 0.75 * modal_disetor
        $need_ekuitas = max(0, ($modal_disetor * 0.75) - $total_ekuitas);

        // 3. PPAP: butuh cadangan_piutang_terbentuk >= ppap_wajib_minimum
        $need_ppap = max(0, $ppap_wajib_minimum - $cadangan_piutang_terbentuk);

        // 4. Likuiditas: butuh kas >= 0.04 * liabilitas_lancar
        $need_kas = max(0, ($liabilitas_lancar * 0.04) - $sum_kas);

        if ($rasio_solvabilitas < 110) {
            $rekomendasi[] = 'PERMODALAN: Rasio Solvabilitas ' . number_format($rasio_solvabilitas, 2) . '% di bawah batas minimum 110%. '
                . 'Cara perbaikan: '
                . '(a) Tambahkan minimal ' . $rp($need_solvabilitas) . ' ke rekening Kas (1.1.01) atau Bank (1.1.02) dari setoran modal/hibah; '
                . '(b) Naikkan ekuitas di rekening Simpanan Pokok (3.1.01), Simpanan Wajib (3.1.02), atau Modal Hibah (3.1.03) minimal ' . $rp($target_ekuitas_solv) . '; '
                . '(c) Atau kurangi liabilitas dengan membayar jatuh tempo simpanan anggota / utang bank tepat waktu. '
                . 'Setelah penyesuaian, target Total Aset minimal ' . $rp($total_liabilitas * 1.10) . ' (110% dari Liabilitas ' . $rp($total_liabilitas) . ').';
        }
        if ($rasio_ekuitas < 75) {
            $rekomendasi[] = 'PERMODALAN: Rasio Ekuitas ' . number_format($rasio_ekuitas, 2) . '% di bawah minimum 75%. '
                . 'Cara perbaikan: '
                . '(a) Tambahkan setoran Simpanan Pokok anggota baru/rekrut anggota baru di rekening 3.1.01; '
                . '(b) Naikkan Simpanan Wajib di rekening 3.1.02 dengan menambah iuran bulanan; '
                . '(c) Setorkan modal hibah/donasi ke rekening 3.1.03 (Modal Penyertaan); '
                . '(d) Alokasikan SHU tahun berjalan secara periodik ke rekening 3.2.xx (Cadangan) daripada dibagikan penuh. '
                . 'Total Ekuitas minimal ' . $rp($modal_disetor * 0.75) . ' (75% dari Modal Disetor ' . $rp($modal_disetor) . '), sehingga perlu tambahan sekitar ' . $rp($need_ekuitas) . '.';
        }
        if ($rasio_npl_neto > 5) {
            $target_outstanding = $outstanding_pinjaman;
            $max_npl_nominal = $target_outstanding * 0.05;
            $kelebihan_npl = max(0, $npl_neto - $max_npl_nominal);
            $rekomendasi[] = 'KUALITAS ASET: NPL Neto ' . number_format($rasio_npl_neto, 2) . '% melebihi batas 5%. '
                . 'Cara perbaikan: '
                . '(a) Kurangi NPL Neto menjadi maksimal ' . $rp($max_npl_nominal) . ' (5% dari Outstanding ' . $rp($outstanding_pinjaman) . '), selisih yang harus diturunkan sekitar ' . $rp($kelebihan_npl) . '; '
                . '(b) Restrukturisasi pinjaman di kolektibilitas Kurang Lancar/Diragukan (kurangi tunggakan pokok); '
                . '(c) Lunasi atau hapus buku pinjaman Macet (setelah melalui mekanisme Penghapusan Piutang ke rekening 1.1.14 / Cadangan); '
                . '(d) Terapkan early warning system: monitoring angsuran ke-3 belum bayar → kirim surat peringatan; ke-4 → kunjungan penagih; ke-6 → serahkan ke collection agent; '
                . '(e) Perketat analisis kelayakan agunan di awal pemberian pinjaman (rasio coverage agunan minimal 125%).';
        }
        if ($ppap_coverage < 100) {
            $rekomendasi[] = 'KUALITAS ASET: Cadangan PPAP yang terbentuk baru ' . number_format($ppap_coverage, 2) . '% dari PPAP wajib minimum. '
                . 'Cara perbaikan: '
                . '(a) Tambahkan penyisihan PPAP ke rekening Cadangan Piutang (1.1.14) sebesar ' . $rp($need_ppap) . ' agar PPAP coverage menjadi 100%; '
                . '(b) Sumber dana: alokasikan sebagian SHU tahun berjalan, atau catat sebagai beban pada laba rugi; '
                . '(c) Formulasi penyisihan per kolektibilitas: DPK 5% dari saldo, Kurang Lancar 15%, Diragukan 50%, Macet 100%; '
                . '(d) Review saldo pinjaman per kolektibilitas per bulan dan sesuaikan saldo rekening 1.1.14 agar sesuai rumus di atas. '
                . 'PPAP wajib minimum saat ini: ' . $rp($ppap_wajib_minimum) . ', PPAP terbentuk: ' . $rp($cadangan_piutang_terbentuk) . '.';
        }
        if ($rasio_likuiditas < 4) {
            $rekomendasi[] = 'LIKUIDITAS: Rasio Kas & Setara Kas terhadap Liabilitas Lancar hanya ' . number_format($rasio_likuiditas, 2) . '% (minimum 4%). '
                . 'Cara perbaikan: '
                . '(a) Tambahkan saldo Kas (1.1.01) atau Bank (1.1.02) minimal ' . $rp($need_kas) . ' untuk mencapai rasio 4% dari Liabilitas Lancar ' . $rp($liabilitas_lancar) . '; '
                . '(b) Cara mengisi: percepat penagihan angsuran pinjaman, tunda pencairan pinjaman baru sampai rasio terpenuhi, tarik simpanan berjangka yang jatuh tempo; '
                . '(c) Diversifikasi penempatan dana likuid: porsi 60% di rekening giro bank (1.1.02.01), 30% di deposito on-call (1.1.02.02), 10% kas tunai (1.1.01); '
                . '(d) Monitor jadwal jatuh tempo liabilitas jangka pendek (Simpanan Sukarela 2.1.02 dan Simpanan Berjangka ≤1 tahun 2.1.03) dan pastikan dana tersedia H-7 sebelum jatuh tempo; '
                . '(e) Target saldo kas & setara kas minimal ' . $rp($liabilitas_lancar * 0.04) . '.';
        }
        if ($roa < 0.5) {
            $target_roa = $total_aset * 0.005;
            $gap_laba = max(0, $target_roa - $laba_bersih);
            $rekomendasi[] = 'RENTABILITAS: ROA tercapai ' . number_format($roa, 2) . '% (target ≥0,5%). '
                . 'Cara perbaikan: '
                . '(a) Tingkatkan laba bersih menjadi minimal ' . $rp($target_roa) . ' (gap sekitar ' . $rp($gap_laba) . ') dengan: '
                . '   • Naikkan margin jasa pinjaman: review prosentase jasa pada rekening 4.1.xx (Pendapatan Jasa Pinjaman), '
                . '   • Kurangi beban operasional: efisiensi pos Beban Gaji (5.1.01), Beban ATK (5.1.02), Beban Administrasi (5.1.03); '
                . '(b) Tingkatkan aset produktif: alirkan dana dari Kas/Bank yang berlebih ke Piutang Pinjaman (1.1.03) untuk menghasilkan bunga; '
                . '(c) Review portofolio pinjaman: fokus pada produk dengan margin tertinggi dan NPL terendah. '
                . 'Pendapatan saat ini: ' . $rp($pendapatan) . ', Biaya: ' . $rp($biaya) . ', Laba: ' . $rp($laba_bersih) . '.';
        }
        $rekomendasi[] = 'MANAJEMEN: Dokumentasikan secara berkala hasil rapat pengurus, kebijakan manajemen risiko, serta kepatuhan terhadap SOP. Tingkatkan kompetensi sumber daya manusia melalui pelatihan literasi keuangan dan tata kelola.';

        if (empty(array_filter([
            $rasio_solvabilitas < 110,
            $rasio_ekuitas < 75,
            $rasio_npl_neto > 5,
            $ppap_coverage < 100,
            $rasio_likuiditas < 4,
            $roa < 0.5,
        ]))) {
            $rekomendasi[] = 'PERTAHANKAN: Kinerja keuangan LKM saat ini telah memenuhi seluruh rasio POJK. Pertahankan praktik baik yang sudah berjalan, lakukan monitoring berkala, dan persiapkan rencana tindak lanjut untuk mencegah deviasi.';
        }

        $data['analisis'] = [
            'total_aset' => $total_aset,
            'total_liabilitas' => $total_liabilitas,
            'kas_setara_kas' => $sum_kas,
            'liabilitas_lancar' => $liabilitas_lancar,
            'modal_disetor' => $modal_disetor,
            'total_ekuitas' => $total_ekuitas,
            'outstanding_pinjaman' => $outstanding_pinjaman,
            'npl_neto' => $npl_neto,
            'cadangan_ppap_terbentuk' => $cadangan_piutang_terbentuk,
            'ppap_wajib_minimum' => $ppap_wajib_minimum,
            'kolek_items' => $kolek_items,
            'sum_kolek_total' => $sum_kolek_total,
            'laba_bersih' => $laba_bersih,
            'pendapatan' => $pendapatan,
            'biaya' => $biaya,
            'roa' => $roa,
            'rasio_solvabilitas' => $rasio_solvabilitas,
            'rasio_ekuitas' => $rasio_ekuitas,
            'rasio_npl_neto' => $rasio_npl_neto,
            'rasio_likuiditas' => $rasio_likuiditas,
            'ppap_coverage' => $ppap_coverage,
            'skor_permodalan' => $skor_permodalan,
            'skor_kualitas_aset' => $skor_kualitas_aset,
            'skor_manajemen' => $skor_manajemen,
            'skor_rentabilitas' => $skor_rentabilitas,
            'skor_likuiditas' => $skor_likuiditas,
            'skor_komposit' => $skor_komposit,
            'pk' => $pk,
            'pk_label' => $pk_label,
            'status_pengawasan' => $status_pengawasan,
            'status_pengawasan_label' => $status_pengawasan_label,
            'status_pengawasan_warna' => $status_pengawasan_warna,
            'status_pengawasan_alasan' => $status_pengawasan_alasan,
            'rekomendasi' => $rekomendasi,
        ];

        $data['laporan'] = 'Penilaian Tingkat Kesehatan POJK';
        $view = view('pelaporan.view.pojk.penilaian_tingkat_kesehatan', $data)->render();

        if ($data['type'] == 'pdf') {
            $paperSize = Session::get('lokasi') == 109 ? [0, 0, 595.28, 935.43] : 'A4';

            $pdf = PDF::loadHTML($view)->setPaper($paperSize, 'portrait');
            return $pdf->stream();
        } else {
            return $view;
        }
    }
}
