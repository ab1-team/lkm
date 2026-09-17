@php
    use App\Utils\Tanggal;
    use App\Models\RencanaAngsuran;

    $rowspan = 19;
    if ($pinkel->real_count > 16) {
        $rowspan = $pinkel->real_count + 3;
    }

    $ketua = $pinkel->kelompok->ketua;
    $sekretaris = $pinkel->kelompok->sekretaris;
    $bendahara = $pinkel->kelompok->bendahara;
    if ($pinkel->struktur_kelompok) {
        $struktur_kelompok = json_decode($pinkel->struktur_kelompok, true);
        $ketua = isset($struktur_kelompok['ketua']) ? $struktur_kelompok['ketua'] : '';
        $sekretaris = isset($struktur_kelompok['sekretaris']) ? $struktur_kelompok['sekretaris'] : '';
        $bendahara = isset($struktur_kelompok['bendahara']) ? $struktur_kelompok['bendahara'] : '';
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ ucwords(str_replace('_', ' ', $laporan)) }}</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        @media print {
            body {
                margin-top: -10px;
            }
        }

        html {
            margin-bottom: 100px;
        }

        ul,
        ol {
            margin-left: -10px;
            page-break-inside: auto !important;
        }

        header {
            position: fixed;
            top: -10px;
            left: 0px;
            right: 0px;
        }

        table tr th,
        table tr td {
            padding: 2px 4px;
        }

        table tr th {
            font-size: 12px;
        }

        .break {
            page-break-after: always;
        }

        li {
            text-align: justify;
        }

        .l {
            border-left: 1px solid #fff;
        }

        .t {
            border-top: 1px solid #fff;
        }

        .r {
            border-right: 1px solid #fff;
        }

        .b {
            border-bottom: 1px solid #fff;
        }
    </style>
</head>

<body onload="window.print()">
    <main style="position: relative; font-size: 12px;">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td rowspan="7" align="center" width="400">
                    <div>&nbsp; &nbsp;</div>
                    <div>&nbsp; &nbsp;</div>
                    <div>&nbsp; &nbsp;</div>
                    <div>&nbsp; &nbsp;</div>
                </td>
                <td width="150">&nbsp; &nbsp;</td>
                <td width="5" align="center">&nbsp; &nbsp;</td>
                <td width="200">&nbsp; &nbsp;</td>
                <td width="150">&nbsp; &nbsp;</td>
                <td width="5" align="center">&nbsp; &nbsp;</td>
                <td width="200">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td colspan="4">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td colspan="4">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp; &nbsp;</td>
                <td align="center">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
                <td colspan="3">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td colspan="7" class="b t" align="center">&nbsp; &nbsp;</td>
            </tr>
        </table>

        @php
            $rencana_filtered = $pinkel->rencana->filter(function ($r) {
                return $r->wajib_pokok != 0 || $r->wajib_jasa != 0;
            });

            $baris_angsuran = ceil($rencana_filtered->count() / 2);
            $cek = 0;
        @endphp

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td width="5%">&nbsp; &nbsp;</td>
                <td colspan="9" height="30">&nbsp; &nbsp;</td>
                <td width="5%">&nbsp; &nbsp;</td>
            </tr>

            <tr style="font-weight: bold;">
                <th rowspan="{{ $baris_angsuran + 1 }}">&nbsp;</th>
                <th height="30" class="l t b" align="center">&nbsp; &nbsp;</th>
                <th class="l t b" align="center">&nbsp; &nbsp;</th>
                <th class="l t b" align="center">&nbsp; &nbsp;</th>
                <th class="l t b r" align="center">&nbsp; &nbsp;</th>
                <th rowspan="{{ $baris_angsuran + 1 }}">&nbsp;</th>
                <th height="30" class="l t b" align="center">&nbsp; &nbsp;</th>
                <th class="l t b" align="center">&nbsp; &nbsp;</th>
                <th class="l t b" align="center">&nbsp; &nbsp;</th>
                <th class="l t b r" align="center">&nbsp; &nbsp;</th>
                <th rowspan="{{ $baris_angsuran + 1 }}">&nbsp;</th>
            </tr>

            @for ($i = 0; $i < $baris_angsuran; $i++)
                <tr>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }}" align="center">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }}" align="center">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }} r" align="right">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }}" align="center">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }}" align="center">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                    <td class="l {{ $i + 1 == $baris_angsuran ? 'b' : '' }} r" align="right">&nbsp; &nbsp;</td>
                </tr>
            @endfor
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td width="5%" rowspan="{{ $rowspan }}">&nbsp; &nbsp;</td>
                <td width="90%" colspan="9" style="font-weight: bold;" height="30">
                    &nbsp; &nbsp;
                </td>
                <td width="5%" rowspan="{{ $rowspan }}">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <th width="3%" class="l t b" rowspan="2">&nbsp; &nbsp;</th>
                <th width="10%" class="l t b" rowspan="2">&nbsp; &nbsp;</th>
                <th width="22%" class="l t" colspan="2">&nbsp; &nbsp;</th>
                <th width="22%" class="l t" colspan="2">&nbsp; &nbsp;</th>
                <th width="22%"class="l t" colspan="2">&nbsp; &nbsp;</th>
                <th width="11%" class="l t r b" rowspan="2">&nbsp; &nbsp;</th>
            </tr>
            <tr>
                <th width="12%" class="l b t">&nbsp; &nbsp;</th>
                <th width="10%" class="l b t">&nbsp; &nbsp;</th>
                <th width="12%" class="l b t">&nbsp; &nbsp;</th>
                <th width="10%" class="l b t">&nbsp; &nbsp;</th>
                <th width="11%" class="l b t">&nbsp; &nbsp;</th>
                <th width="11%" class="l b t">&nbsp; &nbsp;</th>
            </tr>

            @php
                $jumlah = 0;
            @endphp
            @if ($angsuran)
                @foreach ($pinkel->real as $real)
                    @php
                        $jumlah++;
                        $nomor = $loop->iteration;

                        $b = $nomor + 3 == $rowspan ? 'b' : '';

                        $sign = 'TF';
                        if ($real->transaksi->rekening_debit == '1.1.01.01') {
                            $sign = 'TN';
                        }
                    @endphp
                    @if ($real->id == $idtp)
                        <tr>
                            <td class="l {{ $b }}" align="center">{{ $nomor }}</td>
                            <td class="l {{ $b }}" align="center">
                                {{ Tanggal::tglIndo($real->tgl_transaksi) }}
                            </td>
                            <td class="l {{ $b }}" align="right">{{ number_format($real->realisasi_pokok) }}
                            </td>
                            <td class="l {{ $b }}" align="right">
                                {{ number_format($real->tunggakan_pokok < 0 ? 0 : $real->tunggakan_pokok) }}
                            </td>
                            <td class="l {{ $b }}" align="right">{{ number_format($real->realisasi_jasa) }}
                            </td>
                            <td class="l {{ $b }}" align="right">
                                {{ number_format($real->tunggakan_jasa < 0 ? 0 : $real->tunggakan_jasa) }}
                            </td>
                            <td class="l {{ $b }}" align="right">{{ number_format($real->saldo_pokok) }}
                            </td>
                            <td class="l {{ $b }}" align="right">{{ number_format($real->saldo_jasa) }}</td>
                            <td class="l {{ $b }} r" align="center">
                                {{ $sign }}-{{ $real->id }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td class="l {{ $b }}" align="center">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="center">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="right">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="right">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="right">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="right">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="right">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }}" align="right">&nbsp; &nbsp;</td>
                            <td class="l {{ $b }} r" align="center">&nbsp; &nbsp;</td>
                        </tr>
                    @endif
                @endforeach
            @endif

            @if ($jumlah < 16)
                @for ($i = 1; $i <= 16 - $jumlah; $i++)
                    <tr>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="center">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="center">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }}" align="right">&nbsp; &nbsp;</td>
                        <td class="l {{ $i == 16 - $jumlah ? 'b' : '' }} r" align="center">&nbsp; &nbsp;</td>
                    </tr>
                @endfor
            @endif
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td width="5%" rowspan="5">&nbsp; &nbsp;</td>
                <td colspan="3" height="30">&nbsp; &nbsp;</td>
                <td width="5%" rowspan="5">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td width="350" rowspan="3">
                    &nbsp; &nbsp;
                </td>
                <td width="350" align="center">
                    &nbsp; &nbsp;
                </td>
                <td width="350" align="center">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" height="50">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td width="350" align="center">&nbsp; &nbsp;</td>
                <td width="350" align="center">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td colspan="3" height="10">&nbsp; &nbsp;</td>
            </tr>
            <tr>
                <td colspan="4">&nbsp; &nbsp;</td>
                <td>&nbsp; &nbsp;</td>
            </tr>
        </table>
    </main>
</body>

</html>
