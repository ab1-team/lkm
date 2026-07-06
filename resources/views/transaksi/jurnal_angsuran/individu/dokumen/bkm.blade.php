@php
    $type = 'Individu';
    $kreditRows = $trx->tr_idtp ?? collect();

    $firstDebit = null;
    $firstDebitNama = null;
    $kreditList = collect();
    foreach ($kreditRows as $tr) {
        if ($firstDebit === null) {
            $firstDebit = $tr->rekening_debit;
            $firstDebitNama = optional($tr->rek_debit)->nama_akun;
        }
        $kreditKey = $tr->rekening_kredit;
        if (!$kreditList->contains('kode', $kreditKey)) {
            $kreditList->push([
                'kode' => $kreditKey,
                'nama' => optional($tr->rek_kredit)->nama_akun,
            ]);
        }
    }
    $uniqueRows = $kreditList;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKTI KAS MASUK</title>
    <style>
        body {
            font-size: 9px;
            color: rgba(0, 0, 0, 0.8);
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .box {
            width: 14cm;
            min-height: 9cm;
            border: 2px solid #000;
            padding-top: 16px;
            padding-bottom: 12px;
            padding-right: 22px;
            padding-left: 12px;
            display: flex;
            flex-direction: column;
        }

        .box-header {
            padding-left: 16px;
            padding-right: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.5);
        }

        .flex {
            display: flex;
        }

        .block {
            display: block;
        }

        .fw-bold {
            font-weight: bold;
        }

        .fs-8 {
            font-size: 8px;
        }

        .fs-10 {
            font-size: 10px;
        }

        .fs-12 {
            font-size: 12px;
        }

        .ml-4 {
            margin-left: 4px;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .box-body {
            padding-top: 0px;
            padding-left: 24px;
            padding-right: 24px;
        }

        .ttd-table {
            margin-top: auto;
            padding-top: 20px;
        }

        .ttd-table td {
            vertical-align: top;
            text-align: center;
            width: 33.33%;
        }

        .ttd-space {
            height: 50px;
        }

        .ttd-name {
            border-top: 1px solid rgba(0, 0, 0, 0.4);
            padding-top: 4px;
            font-weight: bold;
        }

        .keterangan {
            padding: 1.5px 4px;
            font-weight: normal;
        }
    </style>
</head>

<body onLoad="window.print()">
    <div class="container">
        <div class="box">
            <div class="box-header flex align-items-center justify-content-between fs-10">
                <div class="flex align-items-center">
                    <img src="<?php echo $gambar; ?>" width="50" height="50">
                    <div class="ml-4">
                        <div class="block fw-bold">{{ strtoupper($kec->nama_lembaga_sort) }}</div>
                        <div class="block fw-bold">
                            {{ strtoupper('Kec. ' . $kec->nama_kec . ' Kab. ' . $kec->kabupaten->nama_kab . ' ' . $kec->kabupaten->nama_prov) }}
                        </div>
                        <div class="block fs-10">{{ 'SK Kemenkumham RI No. ' . $kec->nomor_bh }}</div>
                        <div class="block fs-10">{{ $kec->alamat_kec . ', Telp. ' . $kec->telpon_kec }}</div>
                    </div>
                </div>
                <div class="justify-right">
                    <table>
                        <tr>
                            <td>Nomor</td>
                            <td>:</td>
                            <td><?php echo $trx->idt . '/BKM'; ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td>{{ Tanggal::tglIndo($trx->tgl_transaksi) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="box-body fs-12">
                <table width="100%">
                    <tr>
                        <td colspan="6" class="fs-10" align="center">
                            <h1>BUKTI KAS MASUK</h1>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">Terima Dari</td>
                        <td width="2%">:</td>
                        <td colspan="4" class="keterangan">{{ $type }} {{ ucwords($trx->relasi) }}</td>
                    </tr>
                    <tr>
                        <td width="30%">Keterangan</td>
                        <td width="2%">:</td>
                        <td colspan="4" class="keterangan">
                            {{ ucwords('Angsuran Pokok dan Jasa') }}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">Jumlah</td>
                        <td width="2%">:</td>
                        <td colspan="4" class="keterangan">
                            Rp. {{ number_format($trx->tr_idtp_sum_jumlah, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" valign="top"><b>Kode Akun (D/K)</b></td>
                        <td width="2%" valign="top">:</td>
                        <td colspan="4" class="keterangan" style="padding-top:6px;">
                            @if ($firstDebit !== null)
                                <div>
                                    (D) {{ $firstDebit }}
                                    @if ($firstDebitNama) - {{ $firstDebitNama }} @endif
                                </div>
                            @endif
                            @foreach ($uniqueRows as $k)
                                <div>
                                    (K) {{ $k['kode'] }}
                                    @if ($k['nama']) - {{ $k['nama'] }} @endif
                                </div>
                            @endforeach
                        </td>
                    </tr>

                    @for ($i = 1 + $uniqueRows->count(); $i < 5; $i++)
                        <tr>
                            <td width="30%">&nbsp;</td>
                            <td width="2%">&nbsp;</td>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                    @endfor
                </table>

                <table width="100%" class="ttd-table">
                    <tr>
                        <td>Disetujui,</td>
                        <td>Diverifikasi,</td>
                        <td>Disiapkan Oleh :</td>
                    </tr>
                    <tr>
                        <td><?php echo $kec->sebutan_level_1; ?></td>
                        <td><?php echo $kec->sebutan_level_3; ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="ttd-space">&nbsp;</td>
                        <td class="ttd-space">&nbsp;</td>
                        <td class="ttd-space">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="ttd-name">{{ $dir->namadepan . ' ' . $dir->namabelakang }}</td>
                        <td class="ttd-name">{{ $sekr->namadepan . ' ' . $sekr->namabelakang }}</td>
                        <td class="ttd-name"><?php echo $kec->disiapkan; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
