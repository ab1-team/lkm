<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunLevel1;
use App\Models\ArusKas;
use App\Models\Calk;
use App\Models\Kecamatan;
use App\Models\Rekening;
use App\Models\Saldo;
use App\Models\User;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Http\Request;

class HoldingLaporanController extends Controller
{
    private function validatePeriode(Request $request): array
    {
        $tahun = (int) $request->query('tahun');
        $bulan = $request->query('bulan') ? (int) $request->query('bulan') : 12;
        $hari  = $request->query('hari');

        if (!$hari) {
            $hari = date('t', strtotime("$tahun-$bulan-01"));
        }
        $hari = (int) $hari;

        $tgl_kondisi = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($hari, 2, '0', STR_PAD_LEFT);
        $bulanan = $request->query('bulan') !== null;

        return compact('tahun', 'bulan', 'hari', 'tgl_kondisi', 'bulanan');
    }

    // ──────────────────────────────────────────────
    // 4.1 Neraca
    // ──────────────────────────────────────────────
    public function neraca(Request $request)
    {
        $p = $this->validatePeriode($request);
        $kec = $request->attributes->get('holding_kec');
        $keuangan = new Keuangan;

        $sub_judul = 'Per ' . date('t', strtotime($p['tgl_kondisi'])) . ' '
                   . Tanggal::namaBulan($p['tgl_kondisi']) . ' ' . $p['tahun'];

        $akun1 = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $debit = 0;
        $kredit = 0;
        $data = [];

        foreach ($akun1 as $lev1) {
            $sum_akun1 = 0;
            $akun2Data = [];

            foreach ($lev1->akun2 as $lev2) {
                $akun3Data = [];

                foreach ($lev2->akun3 as $lev3) {
                    $sum_saldo = 0;
                    foreach ($lev3->rek as $rek) {
                        $saldo = $keuangan->komSaldo($rek);
                        if ($rek->kode_akun == '3.2.02.01') {
                            $saldo = $keuangan->laba_rugi($p['tgl_kondisi']);
                        }
                        $sum_saldo += $saldo;
                    }

                    if ($lev1->lev1 == '1') {
                        $debit += $sum_saldo;
                    } else {
                        $kredit += $sum_saldo;
                    }
                    $sum_akun1 += $sum_saldo;

                    $akun3Data[] = [
                        'kode_akun' => $lev3->kode_akun,
                        'nama_akun' => $lev3->nama_akun,
                        'saldo'     => round($sum_saldo, 2),
                    ];
                }

                $akun2Data[] = [
                    'kode_akun' => $lev2->kode_akun,
                    'nama_akun' => $lev2->nama_akun,
                    'saldo'     => round(collect($akun3Data)->sum('saldo'), 2),
                    'akun3'     => $akun3Data,
                ];
            }

            $data[] = [
                'kode_akun' => $lev1->kode_akun,
                'nama_akun' => $lev1->nama_akun,
                'lev1'      => $lev1->lev1,
                'saldo'     => round($sum_akun1, 2),
                'akun2'     => $akun2Data,
            ];
        }

        $total_aset = $debit;
        $total_liab_ekuitas = $kredit;

        return response()->json([
            'success'   => true,
            'laporan'   => 'Neraca',
            'kecamatan' => $kec->nama_kec,
            'tgl_kondisi' => $p['tgl_kondisi'],
            'sub_judul' => $sub_judul,
            'ringkasan' => [
                'total_aset'               => round($total_aset, 2),
                'total_liabilitas_ekuitas' => round($total_liab_ekuitas, 2),
                'selisih'                  => round($total_aset - $total_liab_ekuitas, 2),
            ],
            'data' => $data,
        ]);
    }

    // ──────────────────────────────────────────────
    // 4.2 Laba Rugi
    // ──────────────────────────────────────────────
    public function labaRugi(Request $request)
    {
        $p = $this->validatePeriode($request);
        $kec = $request->attributes->get('holding_kec');
        $keuangan = new Keuangan;

        $jenis = $p['bulanan'] ? 'Bulanan' : 'Tahunan';

        if ($p['bulanan']) {
            $sub_judul = 'Periode ' . Tanggal::tglLatin($p['tahun'] . '-' . $p['bulan'] . '-01')
                       . ' S.D ' . Tanggal::tglLatin($p['tgl_kondisi']);
        } else {
            $sub_judul = 'Tahun ' . $p['tahun'];
        }

        $pph = $keuangan->pph($p['tgl_kondisi'], $jenis);
        $lr = $keuangan->laporan_laba_rugi($p['tgl_kondisi'], $jenis);

        // Format section helper
        $formatSection = function ($items) {
            $result = [];
            foreach ($items as $item) {
                $rekening = [];
                foreach ($item['rek'] as $rek) {
                    $rekening[] = [
                        'kode_akun'       => $rek['kode_akun'],
                        'nama_akun'       => $rek['nama_akun'],
                        'saldo_bln_lalu'  => round($rek['saldo_bln_lalu'], 2),
                        'saldo_periode_ini' => round($rek['saldo'] - $rek['saldo_bln_lalu'], 2),
                        'saldo'           => round($rek['saldo'], 2),
                    ];
                }
                $result[] = [
                    'kode_akun' => $item['kode_akun'],
                    'nama_akun' => $item['nama_akun'],
                    'saldo_bln_lalu'  => round(collect($rekening)->sum('saldo_bln_lalu'), 2),
                    'saldo_periode_ini' => round(collect($rekening)->sum('saldo_periode_ini'), 2),
                    'saldo'     => round(collect($rekening)->sum('saldo'), 2),
                    'rekening'  => $rekening,
                ];
            }
            return $result;
        };

        $pendapatan = $formatSection($lr['pendapatan']);
        $beban = $formatSection($lr['beban']);
        $pendapatanNonOps = $formatSection($lr['pendapatan_non_ops']);
        $bebanNonOps = $formatSection($lr['beban_non_ops']);

        // Ringkasan 3 kolom
        $sum3kol = function ($items) {
            $bln_lalu = collect($items)->sum('saldo_bln_lalu');
            $periode  = collect($items)->sum('saldo_periode_ini');
            $sd_now   = collect($items)->sum('saldo');
            return compact('bln_lalu', 'periode', 'sd_now');
        };

        $sPend = $sum3kol($pendapatan);
        $sBeban = $sum3kol($beban);
        $sPendNop = $sum3kol($pendapatanNonOps);
        $sBebanNop = $sum3kol($bebanNonOps);

        $lr_ops = [
            's_d_bulan_lalu' => round($sPend['bln_lalu'] - $sBeban['bln_lalu'], 2),
            'periode_ini'    => round($sPend['periode'] - $sBeban['periode'], 2),
            's_d_sekarang'   => round($sPend['sd_now'] - $sBeban['sd_now'], 2),
        ];

        $lr_nop = [
            's_d_bulan_lalu' => round($sPendNop['bln_lalu'] - $sBebanNop['bln_lalu'], 2),
            'periode_ini'    => round($sPendNop['periode'] - $sBebanNop['periode'], 2),
            's_d_sekarang'   => round($sPendNop['sd_now'] - $sBebanNop['sd_now'], 2),
        ];

        $sebelum_pajak = [
            's_d_bulan_lalu' => round($lr_ops['s_d_bulan_lalu'] + $lr_nop['s_d_bulan_lalu'], 2),
            'periode_ini'    => round($lr_ops['periode_ini'] + $lr_nop['periode_ini'], 2),
            's_d_sekarang'   => round($lr_ops['s_d_sekarang'] + $lr_nop['s_d_sekarang'], 2),
        ];

        $pphData = [
            's_d_bulan_lalu' => round($pph['bulan_lalu'], 2),
            'periode_ini'    => round($pph['bulan_ini'] - $pph['bulan_lalu'], 2),
            's_d_sekarang'   => round($pph['bulan_ini'], 2),
        ];

        $setelah_pajak = [
            's_d_bulan_lalu' => round($sebelum_pajak['s_d_bulan_lalu'] - $pphData['s_d_bulan_lalu'], 2),
            'periode_ini'    => round($sebelum_pajak['periode_ini'] - $pphData['periode_ini'], 2),
            's_d_sekarang'   => round($sebelum_pajak['s_d_sekarang'] - $pphData['s_d_sekarang'], 2),
        ];

        return response()->json([
            'success'   => true,
            'laporan'   => 'Laba Rugi',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'jenis'      => $jenis,
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'  => $sub_judul,
            ],
            'ringkasan' => [
                'pendapatan'         => round($sPend['sd_now'], 2),
                'beban'              => round($sBeban['sd_now'], 2),
                'pendapatan_non_ops' => round($sPendNop['sd_now'], 2),
                'beban_non_ops'      => round($sBebanNop['sd_now'], 2),
                'lr_operasional'       => $lr_ops,
                'lr_non_operasional'   => $lr_nop,
                'sebelum_pajak'        => $sebelum_pajak,
                'pph'                  => $pphData,
                'setelah_pajak'        => $setelah_pajak,
            ],
            'data' => [
                'pendapatan'         => $pendapatan,
                'beban'              => $beban,
                'pendapatan_non_ops' => $pendapatanNonOps,
                'beban_non_ops'      => $bebanNonOps,
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // 4.3 Arus Kas
    // ──────────────────────────────────────────────
    public function arusKas(Request $request)
    {
        $p = $this->validatePeriode($request);
        $kec = $request->attributes->get('holding_kec');
        $keuangan = new Keuangan;

        $jenis = 'Tahunan';
        $sub_judul = 'Tahun ' . $p['tahun'];

        if ($request->query('semester') == '1') {
            $jenis = 'Semester I';
            $sub_judul = 'Semester I Tahun ' . $p['tahun'];
            $p['tgl_kondisi'] = $p['tahun'] . '-06-30';
            $p['bulan'] = 6;
        } elseif ($request->query('semester') == '2') {
            $jenis = 'Semester II';
            $sub_judul = 'Semester II Tahun ' . $p['tahun'];
            $p['tgl_kondisi'] = $p['tahun'] . '-12-31';
            $p['bulan'] = 12;
        } elseif ($p['bulanan']) {
            $jenis = 'Bulanan';
            $sub_judul = 'Bulan ' . Tanggal::namaBulan($p['tgl_kondisi']) . ' ' . $p['tahun'];
        }

        // Hitung saldo awal (bulan lalu)
        $thn = $p['tahun'];
        $bln = $p['bulan'];
        if ($jenis == 'Tahunan' || $jenis == 'Semester I' || $jenis == 'Semester II') {
            $tgl_lalu = $thn . '-00-00';
        } else {
            $bulan_lalu = $bln - 1;
            if ($bulan_lalu <= 0) {
                $bulan_lalu = 12;
                $thn -= 1;
            }
            $tgl_lalu = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu . '-01'));
        }
        $saldo_awal = $keuangan->saldoKas($tgl_lalu);

        // Ambil data arus kas
        $arusKasModels = ArusKas::where('sub', '0')->with('child')->orderBy('id', 'ASC')->get();

        $data = [];
        $id = 1;
        $array_saldo = [];
        $j_saldo = 0;

        foreach ($arusKasModels as $ak) {
            $isSaldoAwal     = $ak->super_sub == '1';
            $isHeaderSection = $ak->super_sub != '0';
            $isTotalOperasi  = $ak->tipe == 'total_operasi';
            $isTotalInvestasi = $ak->tipe == 'total_investasi';
            $isTotalPendanaan = $ak->tipe == 'total_pendanaan';
            $skipTotal = $isSaldoAwal || $isHeaderSection;

            // Kategori
            $kategori = null;
            if ($isTotalOperasi || (!$skipTotal && count($array_saldo) < 3)) {
                $kategori = 'operasi';
            } elseif ($isTotalInvestasi || (!$skipTotal && count($array_saldo) >= 3 && count($array_saldo) < 5)) {
                $kategori = 'investasi';
            } elseif ($isTotalPendanaan || (!$skipTotal && count($array_saldo) >= 5)) {
                $kategori = 'pendanaan';
            }

            // Tentukan parent label
            $parent = 'masuk';
            if ($isSaldoAwal) {
                $parent = 'saldo_awal';
            } elseif ($isTotalOperasi) {
                $parent = 'total_operasi';
            } elseif ($isTotalInvestasi) {
                $parent = 'total_investasi';
            } elseif ($isTotalPendanaan) {
                $parent = 'total_pendanaan';
            }

            $row = [
                'id'       => $id++,
                'parent'   => $parent,
                'kategori' => $kategori,
                'nama'     => $ak->nama_akun,
                'sub'      => (int) $ak->super_sub,
                'saldo'    => 0.0,
                'detail'   => [],
            ];

            if ($isSaldoAwal) {
                $row['saldo'] = round($saldo_awal, 2);
                $data[] = $row;
                continue;
            }

            // Hitung child
            $detail = [];
            foreach ($ak->child as $child) {
                $arus_kas_val = $keuangan->arus_kas($child->rekening, $p['tgl_kondisi'], $jenis);
                $j_saldo += $arus_kas_val;

                $detail[] = [
                    'id'       => $id++,
                    'kode_akun' => null,
                    'nama_akun' => $child->nama_akun,
                    'saldo'    => round($arus_kas_val, 2),
                ];
            }

            $row['detail'] = $detail;
            $row['saldo'] = round($j_saldo, 2);

            if (!$skipTotal) {
                $array_saldo[] = $j_saldo;
                $j_saldo = 0;
            }

            $data[] = $row;
        }

        // Hitung kas bersih
        $kas_operasi = ($array_saldo[0] ?? 0) - (($array_saldo[1] ?? 0) + ($array_saldo[2] ?? 0));
        $kas_investasi = ($array_saldo[3] ?? 0) - ($array_saldo[4] ?? 0);
        $kas_pendanaan = ($array_saldo[5] ?? 0) - ($array_saldo[6] ?? 0);
        $kenaikan = $kas_operasi + $kas_investasi + $kas_pendanaan;
        $saldo_akhir = $saldo_awal + $kenaikan;

        // Group summary
        $group = [];
        foreach ($arusKasModels as $ak) {
            if ($ak->super_sub != '0') {
                $group[] = [
                    'nama'  => $ak->nama_akun,
                    'saldo' => 0.0,
                ];
            }
        }

        return response()->json([
            'success'   => true,
            'laporan'   => 'Arus Kas',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'jenis'      => $jenis,
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'  => $sub_judul,
            ],
            'ringkasan' => [
                'saldo_awal'        => round($saldo_awal, 2),
                'total_masuk'       => round(collect($data)->where('parent', 'masuk')->sum('saldo'), 2),
                'total_keluar'      => 0.0,
                'kas_operasi'       => round($kas_operasi, 2),
                'kas_investasi'     => round($kas_investasi, 2),
                'kas_pendanaan'     => round($kas_pendanaan, 2),
                'kenaikan_penurunan' => round($kenaikan, 2),
                'saldo_akhir'       => round($saldo_akhir, 2),
                'group'             => $group,
            ],
            'data' => $data,
        ]);
    }

    // ──────────────────────────────────────────────
    // 4.4 Perubahan Ekuitas
    // ──────────────────────────────────────────────
    public function perubahanEkuitas(Request $request)
    {
        $p = $this->validatePeriode($request);
        $kec = $request->attributes->get('holding_kec');
        $keuangan = new Keuangan;

        $sub_judul = $p['bulanan']
            ? 'Bulan ' . Tanggal::namaBulan($p['tgl_kondisi']) . ' ' . $p['tahun']
            : 'Tahun ' . $p['tahun'];

        // Ambil semua rekening ekuitas (lev1=3)
        $rekening1 = Rekening::where('lev1', '3')->where('lev2', '1')->with([
            'kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            }
        ])->orderBy('lev1')->orderBy('lev2')->orderBy('lev3', 'DESC')->orderBy('nama_akun')->get();

        $rekening2 = Rekening::where('lev1', '3')->where('lev2', '2')->with([
            'kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            }
        ])->get();

        $data = [];

        // Proses rekening lev2=1 (Modal)
        foreach ($rekening1 as $rek) {
            $saldo = $keuangan->komSaldo($rek);
            $data[] = [
                'kode_akun'   => $rek->kode_akun,
                'nama_akun'   => $rek->nama_akun,
                'saldo_awal'  => 0.0,
                'mutasi'      => round($saldo, 2),
                'saldo_akhir' => round($saldo, 2),
            ];
        }

        // Proses rekening lev2=2 (Saldo Laba)
        foreach ($rekening2 as $rek) {
            if ($rek->kode_akun == '3.2.02.01') {
                $saldo = $keuangan->laba_rugi($p['tgl_kondisi']);
            } else {
                $saldo = $keuangan->komSaldo($rek);
            }
            $data[] = [
                'kode_akun'   => $rek->kode_akun,
                'nama_akun'   => $rek->nama_akun,
                'saldo_awal'  => 0.0,
                'mutasi'      => round($saldo, 2),
                'saldo_akhir' => round($saldo, 2),
            ];
        }

        // Ringkasan
        $ekuitas_awal = 0;
        $setoran = 0;
        $penarikan = 0;
        $dividen = 0;
        $koreksi = 0;
        $laba_rugi = 0;

        foreach ($data as $row) {
            $kode = $row['kode_akun'];
            if ($kode == '3.1.01.01' || $kode == '3.1.01.02' || $kode == '3.1.01.03') {
                $ekuitas_awal += $row['mutasi'];
            }
            if ($kode == '3.2.01.01') {
                $setoran = $row['mutasi'];
            }
            if ($kode == '3.2.01.02') {
                $penarikan = $row['mutasi'];
            }
            if ($kode == '3.2.01.03') {
                $dividen = $row['mutasi'];
            }
            if ($kode == '3.2.02.01') {
                $laba_rugi = $row['mutasi'];
            }
        }

        $ekuitas_akhir = $ekuitas_awal + $setoran + $penarikan + $dividen + $koreksi + $laba_rugi;

        return response()->json([
            'success'   => true,
            'laporan'   => 'Perubahan Ekuitas',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'   => $sub_judul,
            ],
            'ringkasan' => [
                'ekuitas_awal'  => round($ekuitas_awal, 2),
                'setoran'       => round($setoran, 2),
                'penarikan'     => round($penarikan, 2),
                'dividen'       => round($dividen, 2),
                'koreksi'       => round($koreksi, 2),
                'laba_rugi'     => round($laba_rugi, 2),
                'ekuitas_akhir' => round($ekuitas_akhir, 2),
            ],
            'data' => $data,
        ]);
    }

    // ──────────────────────────────────────────────
    // 4.5 CALK (Bagian C)
    // ──────────────────────────────────────────────
    public function calk(Request $request)
    {
        $p = $this->validatePeriode($request);
        $kec = $request->attributes->get('holding_kec');
        $keuangan = new Keuangan;

        $sub_judul = $p['bulanan']
            ? 'Bulan ' . Tanggal::namaBulan($p['tgl_kondisi']) . ' Tahun ' . $p['tahun']
            : 'Tahun ' . $p['tahun'];

        // Query sama seperti neraca tapi sampai level 4 (rekening)
        $akun1 = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $debit = 0;
        $kredit = 0;
        $rincian_akun = [];

        foreach ($akun1 as $lev1) {
            $sum_akun1 = 0;
            $akun2Data = [];

            foreach ($lev1->akun2 as $lev2) {
                $akun3Data = [];

                foreach ($lev2->akun3 as $lev3) {
                    $sum_saldo = 0;
                    $rekeningData = [];

                    foreach ($lev3->rek as $rek) {
                        $saldo = $keuangan->komSaldo($rek);
                        if ($rek->kode_akun == '3.2.02.01') {
                            $saldo = $keuangan->laba_rugi($p['tgl_kondisi']);
                        }
                        $sum_saldo += $saldo;

                        $rekeningData[] = [
                            'kode_akun' => $rek->kode_akun,
                            'nama_akun' => $rek->nama_akun,
                            'saldo'     => round($saldo, 2),
                        ];
                    }

                    if ($lev1->lev1 == '1') {
                        $debit += $sum_saldo;
                    } else {
                        $kredit += $sum_saldo;
                    }
                    $sum_akun1 += $sum_saldo;

                    $akun3Data[] = [
                        'kode_akun' => $lev3->kode_akun,
                        'nama_akun' => $lev3->nama_akun,
                        'saldo'     => round($sum_saldo, 2),
                        'rekening'  => $rekeningData,
                    ];
                }

                $akun2Data[] = [
                    'kode_akun' => $lev2->kode_akun,
                    'nama_akun' => $lev2->nama_akun,
                    'saldo'     => round(collect($akun3Data)->sum('saldo'), 2),
                    'akun3'     => $akun3Data,
                ];
            }

            $rincian_akun[] = [
                'kode_akun' => $lev1->kode_akun,
                'nama_akun' => $lev1->nama_akun,
                'lev1'      => $lev1->lev1,
                'saldo'     => round($sum_akun1, 2),
                'akun2'     => $akun2Data,
            ];
        }

        // Narasi CALK
        $calkModel = Calk::where([
            ['lokasi', $kec->id],
            ['tanggal', 'LIKE', $p['tahun'] . '-' . str_pad($p['bulan'], 2, '0', STR_PAD_LEFT) . '%'],
        ])->first();

        // Penandatangan
        $sekr = User::where('level', '1')->where('jabatan', '2')->where('lokasi', $kec->id)->first();
        $bend = User::where('level', '1')->where('jabatan', '3')->where('lokasi', $kec->id)->first();
        $pengawas = User::where('level', '3')->where('jabatan', '1')->where('lokasi', $kec->id)->first();
        $dir = User::where('level', '1')->where('jabatan', '1')->where('lokasi', $kec->id)->first();

        // Saldo CALK
        $saldo_calk = Saldo::where('kode_akun', $kec->kd_kec)->where('tahun', $p['tahun'])->get();

        // point_a narasi
        $point_a = $calkModel ? $calkModel->catatan : null;

        return response()->json([
            'success'   => true,
            'laporan'   => 'Catatan Atas Laporan Keuangan (CALK)',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'   => $sub_judul,
                'tgl_mad'     => null,
            ],
            'ringkasan' => [
                'point_a'                  => $point_a,
                'total_aset'               => round($debit, 2),
                'total_liabilitas_ekuitas' => round($kredit, 2),
                'selisih'                  => round($debit - $kredit, 2),
            ],
            'data' => [
                'point_a'       => $point_a,
                'catatan'       => $calkModel ? $calkModel->catatan : null,
                'rincian_akun'  => $rincian_akun,
                'saldo_calk'    => $saldo_calk,
                'penandatangan' => [
                    'sekretaris' => $sekr ? ['id' => $sekr->id, 'name' => $sekr->name] : null,
                    'bendahara'  => $bend ? ['id' => $bend->id, 'name' => $bend->name] : null,
                    'pengawas'   => $pengawas ? ['id' => $pengawas->id, 'name' => $pengawas->name] : null,
                    'direktur'   => $dir ? ['id' => $dir->id, 'name' => $dir->name] : null,
                ],
            ],
        ]);
    }
}
