<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\PinjamanAnggota;
use App\Models\PinjamanIndividu;
use App\Models\PinjamanKelompok;
use App\Models\RealAngsuran;
use App\Models\RealAngsuranI;
use App\Models\RealSimpanan;
use App\Models\RencanaAngsuran;
use App\Models\RencanaAngsuranI;
use App\Models\Simpanan;
use App\Models\Transaksi;
use App\Utils\Keuangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use URL;

class GenerateController extends Controller
{
    public function index()
    {
        $kec = Kecamatan::where('web_kec', explode('//', URL::to('/'))[1])
            ->orWhere('web_alternatif', explode('//', URL::to('/'))[1])
            ->first();

        Session::put('lokasi', $kec->id);

        $logo = '/assets/img/icon/favicon.png';
        if ($kec->logo) {
            $logo = '/storage/logo/' . $kec->logo;
        }

        return view('generate.index')->with(compact('logo'));
    }

    public function individu()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $database = env('DB_DATABASE', 'siupk_lkm');
        $table = 'pinjaman_anggota_' . Session::get('lokasi');

        $strukturTabel = \DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA='$database'
            ORDER BY ORDINAL_POSITION;
        ");

        $struktur = array_map(function ($kolom) {
            return $kolom->COLUMN_NAME;
        }, $strukturTabel);

        return response()->json([
            'view' => view('generate.partials.individu')->with(compact('struktur', 'kec'))->render()
        ]);
    }

    public function kelompok()
    {
        $database = env('DB_DATABASE', 'siupk_lkm');
        $table = 'pinjaman_kelompok_' . Session::get('lokasi');

        $strukturTabel = \DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA='$database'
            ORDER BY ORDINAL_POSITION;
        ");

        $struktur = array_map(function ($kolom) {
            return $kolom->COLUMN_NAME;
        }, $strukturTabel);

        return response()->json([
            'view' => view('generate.partials.kelompok')->with(compact('struktur'))->render()
        ]);
    }

    public function generate(Request $request, $offset = 0)
    {
        $real = [];
        $rencana = [];
        $is_pinkel = ($request->pinjaman == 'kelompok') ? true : false;
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        $where = [];
        $whereIn = [];
        $whereNotIn = [];
        foreach ($request->all() as $key => $val) {
            if ($key == '_token' || $key == 'pinjaman') {
                continue;
            }

            $opt = '=';
            $value = $val;
            if (is_array($val)) {
                $opt = $val['operator'];
                $value = $val['value'];
                if (!$value) {
                    continue;
                }

                if ($opt == 'IN') {
                    $values = explode(',', $value);

                    $value = [];
                    foreach ($values as $v) {
                        $whereIn[$key][] = $v;
                    }

                    continue;
                }

                if ($opt == 'NOT IN') {
                    $values = explode(',', $value);

                    $value = [];
                    foreach ($values as $v) {
                        $whereNotIn[$key][] = $v;
                    }

                    continue;
                }
            }

            $where[] = [$key, $opt, $value];
        }

        $limit = 30;
        if ($is_pinkel) {
            $pinjaman = PinjamanKelompok::where($where)->with([
                'sis_pokok',
                'sis_jasa',
                'trx' => function ($query) {
                    $query->where('idtp', '!=', '0');
                },
                'trx.tr_idtp',
                'kelompok',
                'kelompok.d'
            ]);
        } else {
            $pinjaman = PinjamanIndividu::where($where)->with([
                'sis_pokok',
                'sis_jasa',
                'trx' => function ($query) {
                    $query->where('idtp', '!=', '0');
                },
                'trx.tr_idtp',
                'anggota',
                'anggota.d'
            ]);
        }

        if (count($whereIn) > 0) {
            foreach ($whereIn as $key => $value) {
                $pinjaman = $pinjaman->whereIn($key, $value);
            }
        }

        if (count($whereNotIn) > 0) {
            foreach ($whereNotIn as $key => $value) {
                $pinjaman = $pinjaman->whereNotIn($key, $value);
            }
        }

        $pinjaman = $pinjaman->limit($limit)->offset($offset)->orderBy('id', 'ASC')->get();

        $data_id_pinj = [];
        $data_id_real = [];
        foreach ($pinjaman as $pinkel) {
            $data_id_pinj[] = $pinkel->id;

            if ($pinkel->status == 'P') {
                $alokasi = $pinkel->proposal;
                $tgl_cair = $pinkel->tgl_proposal;
            } elseif ($pinkel->status == 'V') {
                $alokasi = $pinkel->verifikasi;
                $tgl_cair = $pinkel->tgl_verifikasi;
            } elseif ($pinkel->status == 'W') {
                $alokasi = $pinkel->alokasi;
                $tgl_cair = $pinkel->tgl_cair;

                if ($tgl_cair == "0000-00-00") {
                    $tgl_cair = $pinkel->tgl_tunggu;
                }
            } else {
                $alokasi = $pinkel->alokasi;
                $tgl_cair = $pinkel->tgl_cair;

                if ($tgl_cair == "0000-00-00") {
                    $tgl_cair = $pinkel->tgl_tunggu;
                }
            }

            $jenis_jasa = $pinkel->jenis_jasa;
            $jangka = $pinkel->jangka;
            $sa_pokok = $pinkel->sistem_angsuran;
            $sa_jasa = $pinkel->sa_jasa;
            $pros_jasa = $pinkel->pros_jasa;

            $index = 1;
            $jumlah_angsuran = $jangka;
            if ($kec->jdwl_angsuran == '1') {
                $index = 0;
                $jumlah_angsuran = $jangka - 1;
                $tgl_cair = date('Y-m-d', strtotime(' 0 month', strtotime($tgl_cair)));
            }

            $simpan_tgl = $tgl_cair;
            if ($is_pinkel) {
                $desa = $pinkel->kelompok->d;
            } else {
                $desa = $pinkel->anggota->d;
            }

            $tgl_angsur = $tgl_cair;
            $tanggal_cair = date('d', strtotime($tgl_cair));

            if ($desa) {
                if ($desa->jadwal_angsuran_desa > 0) {
                    $angsuran_desa = $desa->jadwal_angsuran_desa;
                    if ($angsuran_desa > 0) {
                        $tgl_pinjaman = date('Y-m', strtotime($tgl_cair));
                        $tgl_cair = $tgl_pinjaman . '-' . $angsuran_desa;
                    }
                }
            }

            if ($kec->batas_angsuran > 0) {
                $batas_tgl_angsuran = $kec->batas_angsuran;
                if ($tanggal_cair >= $batas_tgl_angsuran) {
                    $tgl_cair = date('Y-m-d', strtotime('+1 month', strtotime($tgl_cair)));
                }
            }

            $sistem_pokok = ($pinkel->sis_pokok) ? $pinkel->sis_pokok->sistem : '1';
            $sistem_jasa = ($pinkel->sis_jasa) ? $pinkel->sis_jasa->sistem : '1';
            
            if ($sa_pokok == 11) {
                $tempo_pokok        = ($jangka) - 24 / $sistem_pokok;
            } else if ($sa_pokok == 14) {
                $tempo_pokok        = ($jangka) - 3 / $sistem_pokok;
            } else if ($sa_pokok == 15) {
                $tempo_pokok        = ($jangka) - 2 / $sistem_pokok;
            } else if ($sa_pokok == 20) {
                $tempo_pokok        = ($jangka) - 12 / $sistem_pokok;
            } else {
                $tempo_pokok        = $jangka / $sistem_pokok;
                $tempo_pokok = floor($tempo_pokok);
            }

            if ($sa_jasa == 11) {
                $tempo_jasa        = ($jangka) - 24 / $sistem_jasa;
            } else if ($sa_jasa == 14) {
                $tempo_jasa        = ($jangka) - 3 / $sistem_jasa;
            } else if ($sa_jasa == 15) {
                $tempo_jasa        = ($jangka) - 2 / $sistem_jasa;
            } else if ($sa_jasa == 20) {
                $tempo_jasa        = ($jangka) - 12 / $sistem_jasa;
            } else {
                $tempo_jasa        = $jangka / $sistem_jasa;
                $tempo_jasa = floor($tempo_jasa);
            }
            $ra = [];
            $alokasi_pokok = $alokasi;
            $sum_angsuran_jasa = 0;
            for ($j = $index; $j <= $jumlah_angsuran; $j++) {
                $sisa = $j % $sistem_jasa;
                $ke = $j / $sistem_jasa;
                $alokasi_jasa = Keuangan::pembulatan($alokasi_pokok * ($pros_jasa / 100));
                $wajib_jasa = $alokasi_jasa / $tempo_jasa;

                if ($kec->pembulatan != '5000') {
                    $wajib_jasa = Keuangan::pembulatan($wajib_jasa, (string) $kec->pembulatan);
                }

                $sum_jasa = $wajib_jasa * ($tempo_jasa - 1);

                if ($sisa == 0 and $ke != $tempo_jasa and ($sum_angsuran_jasa + $wajib_jasa) < $alokasi_jasa) {
                    $angsuran_jasa = $wajib_jasa;
                } elseif ($sisa == 0 and ($ke == $tempo_jasa || ($sum_angsuran_jasa + $wajib_jasa) >= $alokasi_jasa)) {
                    $angsuran_jasa = $alokasi_jasa - $sum_angsuran_jasa;
                } else {
                    $angsuran_jasa = 0;
                }

                $sum_angsuran_jasa += $angsuran_jasa;
                $ra[$j]['jasa'] = $angsuran_jasa;
            }


            $sum_angsuran_pokok = 0;
            for ($i = $index; $i <= $jumlah_angsuran; $i++) {
                $sisa = $i % $sistem_pokok;
                $ke = $i / $sistem_pokok;

                $wajib_pokok = Keuangan::pembulatan($alokasi / $tempo_pokok, (string) $kec->pembulatan);
                $sum_pokok = $wajib_pokok * ($tempo_pokok - 1);

                if ($sisa == 0 and $ke != $tempo_pokok and ($sum_angsuran_pokok + $wajib_pokok) < $alokasi) {
                    $angsuran_pokok = $wajib_pokok;
                } elseif ($sisa == 0 and ($ke == $tempo_pokok || ($sum_angsuran_pokok + $wajib_pokok) >= $alokasi)) {
                    $angsuran_pokok = $alokasi - $sum_angsuran_pokok;
                } else {
                    $angsuran_pokok = 0;
                }

                $sum_angsuran_pokok += $angsuran_pokok;
				if ($jenis_jasa != '3') {
					$ra[$i]['pokok'] = $angsuran_pokok;
				}
            }

            if ($jenis_jasa == '3') {

                $bunga_per_bulan = ($pros_jasa / 100) / $jangka;
                $angsuran_total = Keuangan::pembulatan(
                    ($alokasi * $bunga_per_bulan) / (1 - pow(1 + $bunga_per_bulan, -$jangka)),
                    (string) $kec->pembulatan
                );

                $sisa_pokok = $alokasi;

                for ($j = $index; $j <= $jumlah_angsuran; $j++) {

                    $jasa = Keuangan::pembulatan(
                        $sisa_pokok * $bunga_per_bulan,
                        (string) $kec->pembulatan
                    );

                    $pokok = $angsuran_total - $jasa;

                    if ($j == $jumlah_angsuran) {
                        $pokok = $sisa_pokok;
                        $angsuran_total = $pokok + $jasa;
                    }

                    $ra[$j]['pokok'] = $pokok;
                    $ra[$j]['jasa']  = $jasa;

                    $sisa_pokok -= $pokok;
                }
            }

            $ra['alokasi'] = $alokasi;

            $target_pokok = 0;
            $target_jasa = 0;

            $data_rencana = [];
            if ($index == 1) {
                $data_rencana[strtotime($tgl_cair)] = [
                    'loan_id' => $pinkel->id,
                    'angsuran_ke' => 0,
                    'jatuh_tempo' => $simpan_tgl,
                    'wajib_pokok' => 0,
                    'wajib_jasa' => 0,
                    'target_pokok' => $target_pokok,
                    'target_jasa' => $target_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => 1
                ];
                $rencana[] = $data_rencana[strtotime($tgl_cair)];
            }

            for ($x = $index; $x <= $jumlah_angsuran; $x++) {
                $bulan  = substr($tgl_cair, 5, 2);
                $tahun  = substr($tgl_cair, 0, 4);
                if ($sa_pokok == 12 || $sa_pokok == 25) {
                    $interval_hari = 7;
                    $tambah = $x * $interval_hari;
                    
                    $jatuh = Carbon::parse($tgl_cair)->addDays($tambah);
                    $jatuh_tempo = $jatuh->toDateString();
                } else {
                    $jatuh = Carbon::parse($tgl_cair)->addMonthsNoOverflow($x);
                    $jatuh_tempo = $jatuh->toDateString();
                }
                // $jatuh_tempo = date('Y-m-t', strtotime($bulan_jatuh_tempo));
                // if (date('d', strtotime($tgl_cair)) < date('d', strtotime($jatuh_tempo))) {
                //     $jatuh_tempo = date('Y-m', strtotime($bulan_jatuh_tempo)) . '-' . date('d', strtotime($tgl_cair));
                // }

                $pokok = $ra[$x]['pokok'];
                $jasa = $ra[$x]['jasa'];

                if ($x == $index) {
                    $target_pokok = $pokok;
                } elseif ($x > $index) {
                    $target_pokok += $pokok;
                }
                if ($x == $index) {
                    $target_jasa = $jasa;
                } elseif ($x > $index) {
                    $target_jasa += $jasa;
                }

                $data_rencana[strtotime($jatuh_tempo)] = [
                    'loan_id' => $pinkel->id,
                    'angsuran_ke' => $x,
                    'jatuh_tempo' => $jatuh_tempo,
                    'wajib_pokok' => $pokok,
                    'wajib_jasa' => $jasa,
                    'target_pokok' => $target_pokok,
                    'target_jasa' => $target_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => 1
                ];
                $rencana[] = $data_rencana[strtotime($jatuh_tempo)];
            }

            $alokasi_pokok = $alokasi;
            $alokasi_jasa = $target_jasa;

            $data_idtp = [];
            $sum_pokok = 0;
            $sum_jasa = 0;

            ksort($data_rencana);
            foreach ($pinkel->trx as $trx) {
                $poko_kredit = '1.1.03';
                $jasa_kredit = '4.1.01';
                $dend_kredit = '4.1.02';

                if (Keuangan::startWith($trx->rekening_kredit, $dend_kredit)) continue;
                if (in_array($trx->idtp, $data_idtp)) continue;

                $tgl_transaksi = $trx->tgl_transaksi;
                $realisasi_pokok = 0;
                $realisasi_jasa = 0;

                foreach ($trx->tr_idtp as $idtp) {
                    if ($is_pinkel) {
                        if ($idtp->id_pinj != $pinkel->id) continue;
                    } else {
                        if ($idtp->id_pinj_i != $pinkel->id) continue;
                    }

                    if (Keuangan::startWith($idtp->rekening_kredit, $poko_kredit)) {
                        $realisasi_pokok = intval($idtp->jumlah);
                        $sum_pokok += $realisasi_pokok;
                        $alokasi_pokok -= $realisasi_pokok;
                    }

                    if (Keuangan::startWith($idtp->rekening_kredit, $jasa_kredit)) {
                        $realisasi_jasa = intval($idtp->jumlah);
                        $sum_jasa += $realisasi_jasa;
                        $alokasi_jasa -= $realisasi_jasa;
                    }
                }

                $ra = [];
                $time_transaksi = strtotime($tgl_transaksi);

                foreach ($data_rencana as $key => $value) {
                    if ($key <= $time_transaksi) {
                        $ra = $value;
                    }
                }

                $target_pokok = 0;
                $target_jasa = 0;
                if ($ra) {
                    $target_pokok = $ra['target_pokok'];
                    $target_jasa = $ra['target_jasa'];
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                $tunggakan_jasa = $target_jasa - $sum_jasa;

                if ($tunggakan_pokok < 0) {
                    $tunggakan_pokok = 0;
                }

                if ($tunggakan_jasa < 0) {
                    $tunggakan_jasa = 0;
                }

                $real[$trx->idtp] = [
                    'id' => $trx->idtp,
                    'loan_id' => $pinkel->id,
                    'tgl_transaksi' => $tgl_transaksi,
                    'realisasi_pokok' => $realisasi_pokok,
                    'realisasi_jasa' => $realisasi_jasa,
                    'sum_pokok' => $sum_pokok,
                    'sum_jasa' => $sum_jasa,
                    'saldo_pokok' => $alokasi_pokok,
                    'saldo_jasa' => $alokasi_jasa,
                    'tunggakan_pokok' => $tunggakan_pokok,
                    'tunggakan_jasa' => $tunggakan_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => 1,
                ];

                $data_id_real[] = $trx->idtp;
                $data_idtp[] = $trx->idtp;
            }
        }

        if ($is_pinkel) {
            RencanaAngsuran::whereIn('loan_id', $data_id_pinj)->delete();
            RealAngsuran::whereIn('loan_id', $data_id_pinj)->delete();

            RencanaAngsuran::insert($rencana);
            RealAngsuran::insert($real);
        } else {
            RencanaAngsuranI::whereIn('loan_id', $data_id_pinj)->delete();
            RealAngsuranI::whereIn('loan_id', $data_id_pinj)->delete();

            RencanaAngsuranI::insert($rencana);
            RealAngsuranI::insert($real);
        }

        $data = $request->all();
        $offset = $offset + $limit;
        return view('generate.generate')->with(compact('data_id_pinj', 'data', 'offset', 'limit'));
    }

    /**
     * Halaman generate simpanan - menampilkan form input CIF
     * dan memproses batch generate real_simpanan
     */
    public function simpanan(Request $request)
    {
        $kec = Kecamatan::where('web_kec', explode('//', URL::to('/'))[1])
            ->orWhere('web_alternatif', explode('//', URL::to('/'))[1])
            ->first();

        if (!$kec) {
            abort(404, 'Wilayah tidak ditemukan untuk domain ini.');
        }

        Session::put('lokasi', $kec->id);

        $id = $request->get('id');
        $start = $request->get('start', 0);
        $limit = $request->get('limit', 25);
        $isMigrate = $request->has('migrate');

        // Jika belum ada parameter id, tampilkan form input
        if (!$request->has('id')) {
            return view('generate.simpanan', [
                'step' => 'form',
            ]);
        }

        // Build query berdasarkan input CIF
        $query = Simpanan::query()->orderBy('id', 'ASC');

        if ($id !== null && $id !== '') {
            $id_array = explode(',', $id);
            $id_array = array_map('trim', $id_array);
            $id_array = array_filter($id_array, 'is_numeric');

            if (!empty($id_array)) {
                $query->whereIn('id', $id_array);
            }
        }

        $total = $query->count();

        // Proses batch
        if ($isMigrate) {
            $start = (int) $start;
        }

        $simpanans = (clone $query)->skip($start)->take($limit)->get();

        foreach ($simpanans as $simp) {
            // Hapus real_simpanan untuk CIF ini
            RealSimpanan::where('cif', $simp->id)->delete();

            // Ambil transaksi terkait
            $transaksis = Transaksi::where('id_simp', $simp->id)
                ->whereNull('deleted_at')
                ->orderBy('tgl_transaksi', 'ASC')
                ->orderBy('urutan', 'ASC')
                ->orderBy('idt', 'ASC')
                ->get();

            $sum = 0;
            foreach ($transaksis as $trx) {
                $real_d = 0;
                $real_k = 0;
                $kode = 0;

                if (
                    str_starts_with($trx->rekening_kredit, '2.1.04.') ||
                    str_starts_with($trx->rekening_kredit, '3.1.01.')
                ) {
                    $real_k = $trx->jumlah;
                    $sum += $trx->jumlah;

                    if ($trx->rekening_debit === '1.1.01.01') {
                        $kode = 1;
                        if ($sum != 0) {
                            $kode = 2;
                        }
                    }
                    if (str_starts_with($trx->rekening_debit, '5.3.04.')) {
                        $kode = 4;
                    }
                } elseif (
                    str_starts_with($trx->rekening_debit, '2.1.04.') ||
                    str_starts_with($trx->rekening_debit, '3.1.01.')
                ) {
                    $real_d = $trx->jumlah;
                    $sum -= $trx->jumlah;

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
                    'cif'           => $simp->id,
                    'idt'           => $trx->idt,
                    'kode'          => $kode,
                    'tgl_transaksi' => $trx->tgl_transaksi,
                    'real_d'        => $real_d,
                    'real_k'        => $real_k,
                    'sum'           => $sum,
                    'lu'            => now(),
                    'id_user'       => $trx->id_user,
                ]);
            }
        }

        $nextStart = $start + $limit;
        $isFinished = $nextStart > $total;

        return view('generate.simpanan', [
            'step' => 'process',
            'id' => $id,
            'total' => $total,
            'start' => $nextStart,
            'limit' => $limit,
            'isFinished' => $isFinished,
        ]);
    }
}
