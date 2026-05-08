<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\DataPemanfaat;
use App\Models\Desa;
use App\Models\JenisaJasa;
use App\Models\JenisProdukPinjaman;
use App\Models\JenisSimpanan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\kode;
use App\Models\PinjamanIndividu;
use App\Models\Rekening;
use App\Models\RealAngsuranI;
use App\Models\RealSimpanan;
use App\Models\RencanaAngsuranI;
use App\Models\Simpanan;
use App\Models\SistemAngsuran;
use App\Models\StatusPinjaman;
use App\Models\Transaksi;
use App\Models\User;
use App\Utils\Keuangan;
use App\Utils\Pinjaman;
use App\Utils\Tanggal;
use DNS1D;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use PDF;
use Session;
use Yajra\DataTables\DataTables;

class SimpananController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $simpanan = Simpanan::with(['anggota', 'js'])
                ->orderBy('id', 'DESC');
            return DataTables::of($simpanan)
                ->addColumn('nama_anggota', function ($row) {
                    return $row->anggota->namadepan ?? '-';
                })
                ->addColumn('jenis_simpanan', function ($row) {
                    return $row->js->nama_js ?? '-';
                })
                ->addColumn('status', function ($row) {
                    $status = '<span class="badge bg-secondary">-</span>';
                    if ($row->status) {
                        $badge = $row->status == 'A' ? 'success' : 'danger';
                        $status = '<span class="badge bg-' . $badge . '">' . ($row->status == 'A' ? 'Aktif' : 'Non-Aktif') . '</span>';
                    }
                    return $status;
                })
                ->addColumn('status', function ($row) {
                    $status = '<span class="badge bg-secondary">-</span>';
                    if ($row->status) {
                        $badge = $row->status == 'A' ? 'success' : 'danger';
                        $status = '<span class="badge bg-' . $badge . '">' . ($row->status == 'A' ? 'Aktif' : 'Non-Aktif') . '</span>';
                    }
                    return $status;
                })
                ->editColumn('jumlah', function ($row) {
                    return 'Rp ' . number_format($row->jumlah, 0, ',', '.');
                })
                ->editColumn('tgl_buka', function ($row) {
                    return date('d/m/Y', strtotime($row->tgl_buka));
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        $title = 'Daftar Simpanan';
        return view('simpanan.index')->with(compact('title'));
    }
    
    public function getTransaksi() {
        $bulan = request()->input('bulan');
        $tahun = request()->input('tahun');
        $cif = request()->input('cif');

        $transaksiQuery = Transaksi::where('id_simp', "$cif");

        if ($tahun != 0) {
            $transaksiQuery->whereYear('tgl_transaksi', $tahun);
        }

        if ($bulan != 0) {
            $transaksiQuery->whereMonth('tgl_transaksi', $bulan);
        }

        $transaksi = $transaksiQuery->with('realSimpanan')->orderBy('tgl_transaksi', 'asc')->get();

        $transaksi->each(function ($item) {
            $item->ins = User::where('id', $item->id_user)->value('ins');
        });

        $bulankop = $bulan;
        $tahunkop = $tahun;
        
    $startDate = \Carbon\Carbon::createFromDate(
        $tahunkop, 
        $bulankop == 0 ? 1 : $bulankop, 
        1
    )->startOfMonth();

    $last_sum = RealSimpanan::where('cif', $cif)
        ->where('tgl_transaksi', '<', $startDate)
        ->orderBy('tgl_transaksi', 'desc')
        ->orderBy('id', 'desc')
        ->value('sum') ?? 0;

    return view('simpanan.partials.detail-transaksi', compact('transaksi', 'bulankop', 'tahunkop', 'cif', 'last_sum'));
}


    public function detailAnggota($id)
    {
        $nia = Simpanan::where('id', $id)->with(['anggota'])->first();
        $title = 'Simpanan $nia->anggota->namadepan';
        return view('simpanan.partials.detail')->with(compact('title', 'nia'));
    }


    public function create()
    {
        $id_angg = request()->get('id_angg');
        $title = 'Registrasi Pinjaman Individu';
        return view('simpanan.create')->with(compact('title', 'id_angg'));
    }

    public function register($id_angg)
    {
        $anggota = Anggota::where('id', $id_angg)->with([
            'Simpanan' => function ($query) {
                $query->orderBy('tgl_buka', 'DESC');
            },
            'pinjaman.sts'
        ])->first();
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $sistem_angsuran = SistemAngsuran::all();
        $js = JenisSimpanan::where(function ($query) {
            $query->where('lokasi', '0')
                ->where('kecuali', 'NOT LIKE', '%#'.session('lokasi').'#%');
        })
        ->orWhere(function ($query) {
            $query->where('lokasi', session('lokasi'))
                ->where('kecuali', 'NOT LIKE', '%#'.session('lokasi').'#%');
        })
        ->orderBy('rek_simp', 'ASC')
        ->get();
            
        $js_dipilih = $js->first()->id;
        
        return view('simpanan.partials.register')->with(compact('anggota', 'kec', 'sistem_angsuran', 'js', 'js_dipilih'));
    }

    public function jenis_simpanan($id, Request $request)
    {
        $nia = $request->input('nia');
        $js = JenisSimpanan::where('id',$id)->first();
        $file = $js->file ?? 1;
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $urutan = Simpanan::where('nia', $nia)->count();
        $anggota = Anggota::where('id', $nia)->first();
        $hubungan = Keluarga::orderBy('kekeluargaan', 'ASC')->get();

        $hubungan_dipilih = 0;

        if ($kec && $anggota) {
            
            $hubungan_dipilih = $kec->hubungan;
            $kec_id = str_pad($kec->id, 3, '0', STR_PAD_LEFT);
            $anggota_id = str_pad($anggota->id, 3, '0', STR_PAD_LEFT);
            $urutan = 1 + $urutan;
            $urutan_formatted = str_pad($urutan, 2, '0', STR_PAD_LEFT);
            $nomor_rekening = "{$id}-{$kec_id}.{$anggota_id}-{$urutan_formatted}";
        } else {
            $nomor_rekening = 'Data tidak valid';
        }
        $fromkuasa = [
            [
                'id' => '1',
                'nama' => 'Pribadi',
            ],
            [
                'id' => '2',
                'nama' => 'Lembaga',
            ],
        ];
        return response()->json([
            'success' => true,
            'view' => view('simpanan.partials.simpanan', compact('id','file', 'kec', 'anggota', 'nomor_rekening', 'fromkuasa', 'hubungan', 'hubungan_dipilih'))->render()
        ]);
    }

    public function Kuasa($id)
    {
        return response()->json([
            'success' => true,
            'view' => view('simpanan.partials.fromkuasa')->with(compact('id'))->render()
        ]);
    }
    public function show(Simpanan $simpanan)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $desa = Desa::where('kd_kec', $kec->kd_kec)->with('sebutan_desa')->get();
        $hubungan = Keluarga::orderBy('kekeluargaan', 'ASC')->get();

        $nia = $simpanan->where('id', $simpanan->id)->with(['anggota', 'js'])->first();
        $title = ucwords($simpanan->anggota->namadepan);
        return view('simpanan.partials.detail')->with(compact('nia', 'title', 'hubungan'));
    }


    public function kop(Simpanan $simpanan)
    {
        $simpanan = $simpanan->where('id', $simpanan->id)->with(['anggota', 'js'])->first();
        $title = 'Cetak KOP buku ' . $simpanan->anggota->namadepan;
        return view('simpanan.partials.cetak_kop')->with(compact('title', 'simpanan'));
    }
    
    public function cetakFormulir(Simpanan $simpanan = null)
    {
        if ($simpanan && $simpanan->id) {
            $simpanan = $simpanan->with(['anggota', 'js'])->find($simpanan->id);
            $title = 'Formulir Simpanan - ' . ($simpanan->anggota->namadepan ?? 'Nasabah');
        } else {
            $simpanan = null;
            $title = 'Formulir Simpanan Nasabah';
        }
    
        return view('simpanan.partials.cetak_formulir')->with(compact('title', 'simpanan'));
    }

    public function cetakSertifikat(Simpanan $simpanan)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $simpanan = $simpanan->where('id', $simpanan->id)->with(['anggota', 'js'])->first();
        $dir = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1'],
        ])->first();
        $transaksi = Transaksi::where('id_simp', $simpanan->id)->with('realSimpanan', 'user')->orderBy('tgl_transaksi', 'asc')->get();

        $title = 'Cetak Sertifikat' . $simpanan->anggota->namadepan;
        return view('simpanan.partials.cetak_sertifikat', compact('title', 'simpanan', 'kec', 'dir'));

    }

    public function koran(Simpanan $simpanan)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $simpanan = $simpanan->where('id', $simpanan->id)->with(['anggota', 'js'])->first();
        $transaksi = Transaksi::where('id_simp', $simpanan->id)->with('realSimpanan', 'user')->orderBy('tgl_transaksi', 'asc')->get();
        $title = 'Cetak Rekening Koran' . $simpanan->anggota->namadepan;
        return view('simpanan.partials.cetak_koran')->with(compact('title','transaksi', 'simpanan', 'kec'));
    }

    public function koran_dtl(Simpanan $simpanan, $bln, $thn)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $simpanan = $simpanan->where('id', $simpanan->id)->with(['anggota', 'js'])->first();

        $query = Transaksi::where('id_simp', $simpanan->id);
    
        if ($thn != 0 && $bln != 0) {
            $query->whereYear('tgl_transaksi', $thn)
                  ->whereMonth('tgl_transaksi', $bln);
            $periode = date('F Y', mktime(0, 0, 0, $bln, 1, $thn));
        } elseif ($thn != 0 && $bln == 0) {
            $query->whereYear('tgl_transaksi', $thn);
            $periode = 'Tahun ' . $thn;
        } elseif ($thn == 0) {
            $periode = 'Semua Periode';
        }
    
        $transaksi = $query->with('realSimpanan', 'user')
                           ->orderBy('tgl_transaksi', 'asc')
                           ->get();

        $title = 'Cetak Rekening Koran ' . $simpanan->anggota->namadepan . ' - ' . $periode;

        return view('simpanan.partials.cetak_koran')->with(compact('title', 'transaksi', 'simpanan', 'kec', 'bln', 'thn'));
    }

    public function cetakKwitansi($idt)
    {
        $transaksi = Transaksi::where('idt', $idt)->first();
        $user = auth()->user();
        $userTransaksi = User::find($transaksi->id_user);
    
        // Logika untuk menentukan user yang ditampilkan
        $userDisplay = ($user->id == $userTransaksi->id) 
            ? $user->ins 
            : $user->ins . ' / ' . $userTransaksi->ins;

        $user = $userDisplay;
        // Menentukan kode berdasarkan jenis rekening
    
        $kode=substr($transaksi->id_simp, 0, 1);

            $title = 'Cetak Pada Kwitansi '.$transaksi->id_simp;

        return view('simpanan.partials.cetak_pada_kwitansi', compact(
            'transaksi',
            'user',
            'title',
            'kode'
        ));
    }

    public function cetakPadaBuku($idt)
    {
        $saldo = 0;
        $transaksi = Transaksi::where('idt', $idt)->whereNull('deleted_at')->with('realSimpanan')->orderBy('tgl_transaksi', 'asc')->first();
        $transaksiCount = Transaksi::where('id_simp', $transaksi->id_simp)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($transaksi) {
                $query->where('tgl_transaksi', '<', $transaksi->tgl_transaksi)
                      ->orWhere(function ($subQuery) use ($transaksi) {
                          $subQuery->where('tgl_transaksi', $transaksi->tgl_transaksi)
                                   ->where('idt', '<=', $transaksi->idt);
                      });
            })
            ->count();

        $user = auth()->user();
        $userTransaksi = User::find($transaksi->id_user);
    
        // Logika untuk menentukan user yang ditampilkan
        $userDisplay = ($user->id == $userTransaksi->id) 
            ? $user->ins 
            : $user->ins . ' / ' . $userTransaksi->ins;
        $user = $userDisplay;
        $kode=substr($transaksi->id_simp, 0, 1);
                        if(in_array(substr($transaksi->id_simp, 0, 1), ['1', '2', '5'])) {
                            $debit = 0;
                            $kredit = $transaksi->jumlah;
                            $saldo += $transaksi->jumlah;
                        } elseif(in_array(substr($transaksi->id_simp, 0, 1), ['3', '4', '6', '7'])) {
                            $debit = $transaksi->jumlah;
                            $kredit = 0;
                            $saldo -= $transaksi->jumlah;
                        } else {
                            $debit = 0;
                            $kredit = 0;
                        }

            $title = 'Cetak Pada Buku '.$transaksi->id_simp;
            return view('simpanan.partials.cetak_pada_buku')->with(compact('title','transaksi', 'transaksiCount', 'kode', 'user', 'debit', 'kredit',  'saldo'));
     }

    public function generateSimpanan($cif)
    {
        try {
            // Hapus semua real_simpanan untuk CIF ini
            RealSimpanan::where('cif', $cif)->delete();

            // Ambil semua transaksi simpanan, urutkan sama persis dengan generate_simpanan.php
            $transaksis = Transaksi::where('id_simp', $cif)
                ->whereNull('deleted_at')
                ->orderBy('tgl_transaksi', 'asc')
                ->orderBy('urutan', 'asc')
                ->orderBy('idt', 'asc')
                ->get();

            $sum = 0;
            foreach ($transaksis as $trx) {
                $real_d = 0;
                $real_k = 0;
                $kode   = 0;

                if (
                    str_starts_with($trx->rekening_kredit, '2.1.04.') ||
                    str_starts_with($trx->rekening_kredit, '3.1.01.')
                ) {
                    $real_k = $trx->jumlah;
                    $sum   += $trx->jumlah;

                    if ($trx->rekening_debit === '1.1.01.01') {
                        $kode = ($sum != 0) ? 2 : 1;
                    }
                    if (str_starts_with($trx->rekening_debit, '5.3.04.')) {
                        $kode = 4;
                    }
                } elseif (
                    str_starts_with($trx->rekening_debit, '2.1.04.') ||
                    str_starts_with($trx->rekening_debit, '3.1.01.')
                ) {
                    $real_d = $trx->jumlah;
                    $sum   -= $trx->jumlah;

                    if ($trx->rekening_kredit === '1.1.01.01') {
                        $kode = 3;
                    }
                    if (str_starts_with($trx->rekening_kredit, '4.1.03.')) {
                        $kode = 5;
                    }
                    if ($trx->rekening_kredit === '2.1.03.01') {
                        $kode = 10;
                    }
                }

                RealSimpanan::create([
                    'cif'            => $cif,
                    'idt'            => $trx->idt,
                    'kode'           => $kode,
                    'tgl_transaksi'  => $trx->tgl_transaksi,
                    'real_d'         => $real_d,
                    'real_k'         => $real_k,
                    'sum'            => $sum,
                    'lu'             => now(),
                    'id_user'        => auth()->id(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Generate real simpanan berhasil. ' . $transaksis->count() . ' transaksi diproses.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function simpanTransaksi(Request $request)
    {
        $jenisMutasi = $request->jenis_mutasi;
        $tglTransaksi = $request->tgl_transaksi;
        $jumlah = $request->jumlah;
        $nomorRekening = $request->nomor_rekening;
        $lembaga = $request->lembaga;
        $jabatan = $request->jabatan;
        $catatan_simpanan = $request->catatan_simpanan;
        $namaDebitur = $request->nama_debitur;
        $cif = $request->nia;

        $simpanan = Simpanan::where('id', $cif)->first();

        if ($simpanan) {
            $parts = explode('/', $tglTransaksi);
            if (count($parts) == 3) {
                $tglTransaksiFormatted = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } else {
                $tglTransaksiFormatted = $tglTransaksi;
            }

            if (strtotime($tglTransaksiFormatted) < strtotime($simpanan->tgl_buka)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Tanggal transaksi tidak boleh lebih kecil dari tanggal buka simpanan (' . 
                                date('d/m/Y', strtotime($simpanan->tgl_buka)) . ')'
                ], 422);
            }
        }

        
        $jenisSimpanan = JenisSimpanan::where('id', $simpanan->jenis_simpanan)->first();
        
        $transaksi = new Transaksi();
        $transaksi->tgl_transaksi = Tanggal::tglNasional($tglTransaksi);
        $transaksi->rekening_debit = $jenisMutasi == '1' ? $jenisSimpanan->rek_kas : $jenisSimpanan->rek_simp;
        $transaksi->rekening_kredit = $jenisMutasi == '1' ? $jenisSimpanan->rek_simp : $jenisSimpanan->rek_kas;
        $transaksi->idtp = 0;
        $transaksi->id_pinj = 0;
        $transaksi->id_pinj_i = 0;
        $transaksi->id_simp = $cif;
        $transaksi->keterangan_transaksi = $jenisMutasi == '1' ? "Setor Tunai Rekening {$nomorRekening}" : "Tarik Tunai Rekening {$nomorRekening}";
        $transaksi->relasi = $namaDebitur;
        $transaksi->jumlah = str_replace(',', '', str_replace('.00', '', $jumlah));
        $transaksi->urutan = 0;
        $transaksi->id_user = auth()->user()->id;
        
        $kode = ($jenisMutasi == 1) ? 2 : 3;
        $real = RealSimpanan::where('cif', $cif)->latest('tgl_transaksi')->latest('lu')->orderBy('id', 'desc')->first();
        $jumlahBersih = str_replace(',', '', str_replace('.00', '', $jumlah));

        $sumSebelumnya = $real ? $real->sum : 0;
        $sumBaru = ($jenisMutasi == 1) 
            ? $sumSebelumnya + $jumlahBersih 
            : $sumSebelumnya - $jumlahBersih;

        if ($transaksi->save()) {
                $maxIdt = Transaksi::max('idt');

                RealSimpanan::create([
                    'cif' => $cif,
                    'idt' => $maxIdt,
                    'kode' => $kode,
                    'tgl_transaksi' => Tanggal::tglNasional($tglTransaksi),
                    'real_d' => ($jenisMutasi == 2) ? $jumlahBersih : 0,
                    'real_k' => ($jenisMutasi == 1) ? $jumlahBersih : 0,
                    'sum' => $sumBaru,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => auth()->user()->id,
                ]);
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan transaksi']);
        }

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_rekening' => 'required', 
            'jenis_simpanan' => 'required',
            'nia' => 'required',
            'setoran_awal' => 'required|numeric',
            'tgl_buka_rekening' => 'required',
            'tgl_minimal_tutup_rekening' => 'required',
            'bunga' => 'required|numeric',
            'pajak_bunga' => 'required|numeric',
            'admin' => 'required|numeric',
            'kuasa' => 'required',
            'ahli_waris' => 'required',
            'hubungan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $simpanan = new Simpanan();
        $simpanan ->nomor_rekening = $request->nomor_rekening;
        $simpanan ->lembaga = $request->lembaga;
        $simpanan ->jabatan = $request->jabatan;
        $simpanan ->catatan_simpanan = $request->catatan_simpanan;
        $simpanan ->jenis_simpanan = $request->jenis_simpanan;
        $simpanan ->nia = $request->nia;
        $simpanan ->jumlah = $request->setoran_awal;
        $simpanan ->tgl_buka = Tanggal::tglNasional($request->tgl_buka_rekening);
        $simpanan ->tgl_tutup = Tanggal::tglNasional($request->tgl_minimal_tutup_rekening);
        $simpanan ->bunga = $request->bunga;
        $simpanan ->pajak = $request->pajak_bunga;
        $simpanan ->admin = $request->admin;
        $simpanan ->status = 'A';
        $simpanan ->sp = $request->kuasa;
        $simpanan ->pengampu = $request->ahli_waris;
        $simpanan ->hubungan = $request->hubungan;
        $simpanan ->user_id = auth()->id();
        $simpanan ->lu = date('Y-m-d H:i:s');
        $simpanan ->save();

        $maxId = Simpanan::max('id');

        $js = JenisSimpanan::where('id', $request->jenis_simpanan)->first();
        $anggota = Anggota::where('id', $request->nia)->first();


        //setoran awal
        Transaksi::create([
            'tgl_transaksi' => Tanggal::tglNasional($request->tgl_buka_rekening),
            'rekening_debit' => $js->rek_kas,
            'rekening_kredit' =>  $js->rek_simp,
            'idtp' => '0',
            'id_pinj' => '0',
            'id_pinj_i' => '0',
            'id_simp' => $maxId,
            'keterangan_transaksi' => 'Setoran Awal ' . $js->nama_js . ' ' . $anggota->namadepan . '',
            'relasi' => $anggota->namadepan . '[' . $request->nia . ']',
            'jumlah' => str_replace(',', '', str_replace('.00', '', $request->setoran_awal)),
            'urutan' => '0',
            'id_user' => auth()->user()->id,
        ]);
        $maxIdt = Transaksi::max('idt');
        
        //admin register
        $jumlahAdmin = str_replace(',', '', str_replace('.00', '', $request->admin_register));

        if ($jumlahAdmin > 0) {
            Transaksi::create([
                'tgl_transaksi' => Tanggal::tglNasional($request->tgl_buka_rekening),
                'rekening_debit' => $js->rek_kas,
                'rekening_kredit' =>  $js->rek_adm,
                'idtp' => '0',
                'id_pinj' => '0',
                'id_pinj_i' => '0',
                'id_simp' => '0',
                'keterangan_transaksi' => 'Pendapatan Admin Simpanan ' . $js->nama_js . ' ' . $anggota->namadepan . '',
                'relasi' => $anggota->namadepan . '[' . $request->nia . ']',
                'jumlah' => $jumlahAdmin,
                'urutan' => '0',
                'id_user' => auth()->user()->id,
            ]);
        }
        //real setoran awalx
        RealSimpanan::create([
            'cif' => $maxId,
            'idt' => $maxIdt,
            'kode' => 1,
            'tgl_transaksi' => Tanggal::tglNasional($request->tgl_buka_rekening),
            'real_d' =>  '0',
            'real_k' => str_replace(',', '', str_replace('.00', '', $request->setoran_awal)),
            'sum' => str_replace(',', '', str_replace('.00', '', $request->setoran_awal)),
            'lu' => date('Y-m-d H:i:s'),
            'id_user' => auth()->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data simpanan berhasil disimpan',
            'id' => $simpanan->id
        ]);
    }

    public function bunga()
    {
        $id_angg = request()->get('id_angg');
        $title = 'Perhitungan Bunga & Biaya';
        return view('simpanan.bunga')->with(compact('title', 'id_angg'));
    }
    
    public function infoBunga() {
        $bulan = request()->input('bulan');
        $tahun = request()->input('tahun');

        $tahun_now = $tahun;

        $tahun = $tahun_now;
        $bulan = $bulan - 1;
        if ($bulan == 0) {
            $bulan = 12;
            $tahun--;
        }

        $tahun_lalu = $tahun;
        $bulan_lalu = $bulan - 1;
        if ($bulan_lalu == 0) {
            $bulan_lalu = 12;
            $tahun_lalu--;
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $jb = $kec->hitung_bunga;
        $tgl_bunga = $kec->tgl_bunga ?? 0;

        $last_day_bulan_lalu = cal_days_in_month(CAL_GREGORIAN, $bulan_lalu, $tahun_lalu);
        $last_day_bulan_ini  = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        if ($tgl_bunga < 0) {
            $day_bunga_lalu = $last_day_bulan_lalu + $tgl_bunga + 1;
            $day_bunga_ini  = $last_day_bulan_ini + $tgl_bunga + 1;

            $day_bunga_lalu = max(1, min($day_bunga_lalu, $last_day_bulan_lalu));
            $day_bunga_ini  = max(1, min($day_bunga_ini, $last_day_bulan_ini));

            $tgl_awal  = sprintf("%04d-%02d-%02d", $tahun_lalu, $bulan_lalu, $day_bunga_lalu);
            $tgl_trans = sprintf("%04d-%02d-%02d", $tahun, $bulan, $day_bunga_ini);
            $tgl_akhir = date("Y-m-d", strtotime($tgl_trans . " -1 day"));
        } else {
            $day_bunga_ini = $tgl_bunga;
            $day_bunga_ini = max(1, min($day_bunga_ini, $last_day_bulan_ini));

            $bulan_depan = $bulan + 1;
            $tahun_depan = $tahun;
            if ($bulan_depan == 13) {
                $bulan_depan = 1;
                $tahun_depan++;
            }

            $last_day_bulan_depan = cal_days_in_month(CAL_GREGORIAN, $bulan_depan, $tahun_depan);
            $day_bunga_depan = max(1, min($tgl_bunga, $last_day_bulan_depan));

            $tgl_awal  = sprintf("%04d-%02d-%02d", $tahun, $bulan, $day_bunga_ini);
            $tgl_trans = sprintf("%04d-%02d-%02d", $tahun_depan, $bulan_depan, $day_bunga_depan);
            $tgl_akhir = date("Y-m-d", strtotime($tgl_trans . " -1 day"));
        }
    
        $bulanTahun = \Carbon\Carbon::parse($tgl_trans)->translatedFormat('F Y');
        $datetime_awal  = new \DateTime($tgl_awal);
        $datetime_akhir = new \DateTime($tgl_akhir);
        $selisih = $datetime_awal->diff($datetime_akhir);
        $jumlah_hari = $selisih->days + 1;

        return view('simpanan.partials.info_hitung_bunga', compact('jumlah_hari', 'tgl_awal', 'tgl_akhir','jb'));
    }


    public function simpanBunga()
    {
        $tahun = request()->get('tahun');
        $bulan = request()->get('bulan');
        $start = request()->get('start');
        $id = request()->get('id');
        $limit = 30;

        if ($id == NULL || $id == "" || $id == 0) {
            $count = Simpanan::where('status', 'A')->count();
            $nia = Simpanan::where('status', 'A')
                           ->orderBy('id', 'asc')
                           ->skip($start)
                           ->take($limit)
                           ->with(['anggota', 'js'])
                           ->get();
        } else {
            $id_array = explode(',', $id);
            $id_array = array_map('trim', $id_array);
            $id_array = array_filter($id_array, 'is_numeric');
    
            $count = Simpanan::whereIn('id', $id_array)
                             ->where('status', 'A')
                             ->count();
            $nia = Simpanan::whereIn('id', $id_array)
                           ->where('status', 'A')
                           ->orderBy('id', 'asc')
                           ->skip($start)
                           ->take($limit)
                           ->with(['anggota', 'js'])
                           ->get();
        }
        $tahun_now = $tahun;

        $tahun = $tahun_now;
        $bulan = $bulan - 1;
        if ($bulan == 0) {
            $bulan = 12;
            $tahun--;
        }

        $tahun_lalu = $tahun;
        $bulan_lalu = $bulan - 1;
        if ($bulan_lalu == 0) {
            $bulan_lalu = 12;
            $tahun_lalu--;
        }

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $tgl_bunga = $kec->tgl_bunga ?? 0;

        $last_day_bulan_lalu = cal_days_in_month(CAL_GREGORIAN, $bulan_lalu, $tahun_lalu);
        $last_day_bulan_ini  = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        if ($tgl_bunga < 0) {
            $day_bunga_lalu = $last_day_bulan_lalu + $tgl_bunga + 1;
            $day_bunga_ini  = $last_day_bulan_ini + $tgl_bunga + 1;

            $day_bunga_lalu = max(1, min($day_bunga_lalu, $last_day_bulan_lalu));
            $day_bunga_ini  = max(1, min($day_bunga_ini, $last_day_bulan_ini));

            $tgl_awal  = sprintf("%04d-%02d-%02d", $tahun_lalu, $bulan_lalu, $day_bunga_lalu);
            $tgl_trans = sprintf("%04d-%02d-%02d", $tahun, $bulan, $day_bunga_ini);
            $tgl_akhir = date("Y-m-d", strtotime($tgl_trans . " -1 day"));
        } else {
            $day_bunga_ini = $tgl_bunga;
            $day_bunga_ini = max(1, min($day_bunga_ini, $last_day_bulan_ini));

            $bulan_depan = $bulan + 1;
            $tahun_depan = $tahun;
            if ($bulan_depan == 13) {
                $bulan_depan = 1;
                $tahun_depan++;
            }

            $last_day_bulan_depan = cal_days_in_month(CAL_GREGORIAN, $bulan_depan, $tahun_depan);
            $day_bunga_depan = max(1, min($tgl_bunga, $last_day_bulan_depan));

            $tgl_awal  = sprintf("%04d-%02d-%02d", $tahun, $bulan, $day_bunga_ini);
            $tgl_trans = sprintf("%04d-%02d-%02d", $tahun_depan, $bulan_depan, $day_bunga_depan);
            $tgl_akhir = date("Y-m-d", strtotime($tgl_trans . " -1 day"));
        }
        $bulanTahun = \Carbon\Carbon::parse($tgl_trans)->translatedFormat('F Y');
    
        $datetime_awal  = new \DateTime($tgl_awal);
        $datetime_akhir = new \DateTime($tgl_akhir);
        $selisih = $datetime_awal->diff($datetime_akhir);
        $jumlah_hari = $selisih->days + 1;

        $hitung_bunga = $kec->hitung_bunga;

        foreach ($nia as $simp) {
            // Validasi: Skip jika tanggal transaksi bunga kurang dari tanggal buka simpanan
            if (strtotime($tgl_trans) < strtotime($simp->tgl_buka)) {
                continue; // Skip simpanan ini
            }
            
                $saldo_terakhir = RealSimpanan::where('cif', $simp->id)
                    ->where('tgl_transaksi', '<', $tgl_awal)
                    ->orderByDesc('tgl_transaksi')
                    ->orderByDesc('id')
                    ->value('sum') ?? 0;

            if ($hitung_bunga == 1) { // Saldo terakhir

                $saldo = RealSimpanan::where('cif', $simp->id)
                    ->where('tgl_transaksi', '<=', $tgl_akhir)
                    ->orderByDesc('tgl_transaksi')
                    ->orderByDesc('id')
                    ->value('sum') ?? 0;

            }elseif($hitung_bunga == 2) { // Saldo terendah

                $saldo = RealSimpanan::where('cif', $simp->id)
                    ->whereBetween('tgl_transaksi', [$tgl_awal, $tgl_akhir])
                    ->min('sum') ?? 0;

                $saldo = min($saldo, $saldo_terakhir);
            } else{ // Saldo rata-rata

                $transaksi = RealSimpanan::where('cif', $simp->id)
                    ->whereBetween('tgl_transaksi', [$tgl_awal, $tgl_akhir])
                    ->orderBy('tgl_transaksi')
                    ->get();

                $jumdeb = 0;
                $jumkre = 0;

                $awal = strtotime($tgl_awal);

                foreach ($transaksi as $real) {

                    $hari_ke = floor(
                        (strtotime($real->tgl_transaksi) - $awal) / 86400
                    ) + 1;

                    $sisa_hari = $jumlah_hari - ($hari_ke - 1);

                    if ($sisa_hari > 0) {
                        $jumdeb += $real->real_d * $sisa_hari;
                        $jumkre += $real->real_k * $sisa_hari;
                    }
                }

                $saldo = $saldo_terakhir
                       + ($jumkre / $jumlah_hari)
                       - ($jumdeb / $jumlah_hari);
            }

            $bunga = 0;
            $pajak = 0;

            $nia = $simp->nia;
            if($simp->sp==1){
                $saldo_nia = Simpanan::where('nia', $nia)
                    ->where('sp', 1)
                    ->get()
                    ->sum(function($simpanan) {
                        return RealSimpanan::where('cif', $simpanan->id)
                            ->orderByDesc('tgl_transaksi')
                            ->orderByDesc('id')
                            ->value('sum') ?? 0;
                    });
            }else{
                $saldo_nia = Simpanan::where('id', $simp->id)
                    ->get()
                    ->sum(function($simpanan) {
                        return RealSimpanan::where('cif', $simpanan->id)
                            ->orderByDesc('tgl_transaksi')
                            ->orderByDesc('id')
                            ->value('sum') ?? 0;
                    });
            }


            if ($kec->min_bunga <= $saldo) {
                $bunga = number_format($saldo * $simp->bunga/100, 0, '.', '');
            }
    
            if ($kec->min_pajak <= $saldo_nia) {
                $pajak = number_format($bunga * $simp->pajak/100, 0, '.', '');
            }
            $admin = $simp->admin;

            $realSimpanan = RealSimpanan::where('cif', $simp->id)
                ->where('tgl_transaksi', '<=', $tgl_trans)
                ->orderBy('id', 'desc')
                ->first();
            
            $jenisSimpanan = JenisSimpanan::where('id', $simp->jenis_simpanan)->first();
            $idmax = Transaksi::max('idt');
                $tanggal_formatted = Tanggal::tglNasional($tgl_trans);
                $bungaExists = Transaksi::where('tgl_transaksi', $tanggal_formatted)
                                   ->where('rekening_debit', $jenisSimpanan->rek_bunga)
                                   ->where('rekening_kredit', $jenisSimpanan->rek_simp)
                                   ->where('id_simp', $simp->id)
                                   ->exists();
                $pajakExists = Transaksi::where('tgl_transaksi', $tanggal_formatted)
                                   ->where('rekening_debit', $jenisSimpanan->rek_simp)
                                   ->where('rekening_kredit', $jenisSimpanan->rek_pajak)
                                   ->where('id_simp', $simp->id)
                                   ->exists();
                $adminExists = Transaksi::where('tgl_transaksi', $tanggal_formatted)
                                   ->where('rekening_debit', $jenisSimpanan->rek_simp)
                                   ->where('rekening_kredit', $jenisSimpanan->rek_adm)
                                   ->where('id_simp', $simp->id)
                                   ->exists();

            if (!$bungaExists && $bunga > 0) {
                $idmax++;
                $sum_baru = $realSimpanan ? $realSimpanan->sum + $bunga : $bunga;

                $transaksi = new Transaksi();
                $transaksi->tgl_transaksi = Tanggal::tglNasional($tgl_trans);
                $transaksi->rekening_debit = $jenisSimpanan->rek_bunga;
                $transaksi->rekening_kredit = $jenisSimpanan->rek_simp;
                $transaksi->idtp = 0;
                $transaksi->id_pinj = 0;
                $transaksi->id_pinj_i = 0;
                $transaksi->id_simp = $simp->id;
                $transaksi->keterangan_transaksi = "Bunga ".$simp->nomor_rekening." ".$simp->anggota->namadepan." bulan ".$bulanTahun;
                $transaksi->relasi = $simp->anggota->namadepan;
                $transaksi->jumlah = $bunga;
                $transaksi->urutan = 0;
                $transaksi->id_user = auth()->user()->id;

                if ($transaksi->save()) {
                    RealSimpanan::create([
                        'cif' => $simp->id,
                        'idt' => $idmax,
                        'kode' => 5,
                        'tgl_transaksi' => $tgl_trans,
                        'real_d' => 0,
                        'real_k' => $bunga,
                        'sum' => $sum_baru,
                        'lu' => now(),
                        'id_user' => $transaksi->id_user,
                    ]);
                }
            }

            if (!$pajakExists && $pajak > 0) {
                $idmax++;
                $sum_baru = $realSimpanan ? $realSimpanan->sum - $pajak : $pajak;

                $transaksi = new Transaksi();
                $transaksi->tgl_transaksi = Tanggal::tglNasional($tgl_trans);
                $transaksi->rekening_debit = $jenisSimpanan->rek_simp;
                $transaksi->rekening_kredit = $jenisSimpanan->rek_pajak;
                $transaksi->idtp = 0;
                $transaksi->id_pinj = 0;
                $transaksi->id_pinj_i = 0;
                $transaksi->id_simp = $simp->id;
                $transaksi->keterangan_transaksi = "Pajak Bunga ".$simp->nomor_rekening." ".$simp->anggota->namadepan." bulan ".$bulanTahun;
                $transaksi->relasi = $simp->anggota->namadepan;
                $transaksi->jumlah = $pajak;
                $transaksi->urutan = 0;
                $transaksi->id_user = auth()->user()->id;
            
                if ($transaksi->save()) {
                    RealSimpanan::create([
                        'cif' => $simp->id,
                        'idt' => $idmax,
                        'kode' => 6,
                        'tgl_transaksi' => $tgl_trans,
                        'real_d' => $pajak,
                        'real_k' => 0,
                        'sum' => $sum_baru,
                        'lu' => now(),
                        'id_user' => $transaksi->id_user,
                    ]);
                }
            }
        
            if (!$adminExists && $admin > 0) {
                $idmax++;
                $sum_baru = $realSimpanan ? $realSimpanan->sum - $admin : $admin;

                $transaksi = new Transaksi();
                $transaksi->tgl_transaksi = Tanggal::tglNasional($tgl_trans);
                $transaksi->rekening_debit = $jenisSimpanan->rek_simp;
                $transaksi->rekening_kredit = $jenisSimpanan->rek_adm;
                $transaksi->idtp = 0;
                $transaksi->id_pinj = 0;
                $transaksi->id_pinj_i = 0;
                $transaksi->id_simp = $simp->id;
                $transaksi->keterangan_transaksi = "Admin ".$simp->nomor_rekening." ".$simp->anggota->namadepan;
                $transaksi->relasi = $simp->anggota->namadepan;
                $transaksi->jumlah = $admin;
                $transaksi->urutan = 0;
                $transaksi->id_user = auth()->user()->id;
            
                if ($transaksi->save()) {
                    RealSimpanan::create([
                        'cif' => $simp->id,
                        'idt' => $idmax,
                        'kode' => 7,
                        'tgl_transaksi' => $tgl_trans,
                        'real_d' => $admin,
                        'real_k' => 0,
                        'sum' => $sum_baru,
                        'lu' => now(),
                        'id_user' => $transaksi->id_user,
                    ]);
                }
            }
        }

        $link = request()->url('');
        $query = request()->query();

        if ($query['id'] == "" || $query['id'] == NULL) {
            $query['id'] = 0;
        }

        $query['start'] = $start + 30;
        $next = $link . '?' . http_build_query($query);

        if ($query['start'] < $count + 30) {
            $persen = round($query['start'] / ($count + 30) * 100);

            echo '<a href="' . $next . '" id="next"></a>';
            echo '
                <style>
                    @keyframes progress {
                        0% { --percentage: 0; }
                        100% { --percentage: var(--value); }
                    }

                    @property --percentage {
                        syntax: "<number>";
                        inherits: true;
                        initial-value: 0;
                    }

                    [role="progressbar"] {
                        --percentage: var(--value);
                        --primary: #369;
                        --secondary: #adf;
                        --size: 200px;
                        animation: progress 1s 0.2s forwards;
                        width: var(--size);
                        aspect-ratio: 1;
                        border-radius: 50%;
                        position: relative;
                        overflow: hidden;
                        display: grid;
                        place-items: center;
                        margin: 100px auto;
                    }

                    [role="progressbar"]::before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: conic-gradient(var(--primary) calc(var(--percentage) * 1%), var(--secondary) 0);
                        mask: radial-gradient(white 55%, transparent 0);
                        mask-mode: alpha;
                        -webkit-mask: radial-gradient(#0000 55%, #000 0);
                        -webkit-mask-mode: alpha;
                    }

                    [role="progressbar"]::after {
                        counter-reset: percentage var(--value);
                        content: counter(percentage) "%";
                        font-family: Helvetica, Arial, sans-serif;
                        font-size: calc(var(--size) / 5);
                        color: var(--primary);
                    }
                </style>

                <div role="progressbar" aria-valuenow="' . $persen . '" aria-valuemin="0" aria-valuemax="100" style="--value: ' . $persen . '"></div>

                <script>document.querySelector("#next").click()</script>
            ';
            exit;
        } else {
            echo '<script>window.opener.postMessage("closed", "*"); window.close();</script>';
            exit;
        }
    }

    public function simpanTransaksiBunga(Request $request)
    {
        $tglTransaksi = now(); 
        $cifInput = $request->cif; // Bisa kosong atau berisi daftar CIF (misal: "2,3,12,34")
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
    
        if (empty($cifInput)) {
            $simpananList = Simpanan::where('status', 'A')
                                    ->with('anggota')
                                    ->get();
        } else {
            $cifArray = explode(',', $cifInput);
            $simpananList = Simpanan::where('status', 'A')
                                    ->whereIn('id', $cifArray)
                                    ->with('anggota')
                                    ->get();
        }
        foreach ($simpananList as $simpanan) {

            $cif = $simpanan->id;
            $nomorRekening = $simpanan->no_rekening;
            $namaDebitur = $simpanan->anggota->namadepan;
            $simpanan = Simpanan::where('id', $cif)->first();
            
            // Validasi: Skip jika tanggal transaksi kurang dari tanggal buka simpanan
            if (strtotime(date('Y-m-d', strtotime($tglTransaksi))) < strtotime($simpanan->tgl_buka)) {
                continue; // Skip simpanan ini
            }
            
            $jenisSimpanan = JenisSimpanan::where('id', $simpanan->jenis_simpanan)->first();

            
            $real = RealSimpanan::where('cif', $cif)->latest('tgl_transaksi')->first();
            $sumSebelumnya = $real ? $real->sum : 0;
            if($sumSebelumnya>=$kec->min_bunga){

            }
            $bunga = 10000;
            $pajak = 10000;
            $admin = 10000;

            $transaksi = new Transaksi();
            $transaksi->tgl_transaksi = Tanggal::tglNasional($tglTransaksi);
            $transaksi->rekening_debit = ($jenisMutasi == '1') ? $jenisSimpanan->rek_kas : $jenisSimpanan->rek_simp;
            $transaksi->rekening_kredit = ($jenisMutasi == '1') ? $jenisSimpanan->rek_simp : $jenisSimpanan->rek_kas;
            $transaksi->idtp = 0;
            $transaksi->id_pinj = 0;
            $transaksi->id_pinj_i = 0;
            $transaksi->id_simp = $cif;
            $transaksi->keterangan_transaksi = ($jenisMutasi == '1') ? "Setor Tunai Rekening {$nomorRekening}" : "Tarik Tunai Rekening {$nomorRekening}";
            $transaksi->relasi = $namaDebitur;
            $transaksi->jumlah = str_replace(',', '', str_replace('.00', '', $jumlah));
            $transaksi->urutan = 0;
            $transaksi->id_user = auth()->user()->id;
        
            $maxIdt = Transaksi::where('id_simp', $cif)->max('idt');
            $sumBaru = ($jenisMutasi == 1) 
                ? $sumSebelumnya + $jumlahBersih 
                : $sumSebelumnya - $jumlahBersih;

                $jm = ($jenisMutasi == 1) ? 2 : 3;
            $kode   = kode::where('def', $jm);

            RealSimpanan::create([
                'cif' => $cif,
                'idt' => $maxIdt,
                'kode' => ($jenisMutasi == 1) ? 2 : 3,
                'tgl_transaksi' => Tanggal::tglNasional($tglTransaksi),
                'real_d' => ($jenisMutasi == 2) ? $jumlahBersih : 0,
                'real_k' => ($jenisMutasi == 1) ? $jumlahBersih : 0,
                'sum' => $sumBaru,
                'lu' => now(),
                'id_user' => auth()->user()->id,
            ]);

            $transaksi->save();
        }

        return response()->json(['success' => true, 'message' => 'Transaksi berhasil disimpan']);
    }



}
