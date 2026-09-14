@php
    use App\Utils\Tanggal;

    $keterangan = '';
    $denda = 0;
    $idt = 0;

    $angsur_bulan_depan = true;

    $target_pokok = 0;
    $target_jasa = 0;
    $angsuran_ke = 1;
    $wajib_pokok = 0;
    $wajib_jasa = 0;
    if ($ra_bulan_ini) {
        $wajib_pokok = $ra_bulan_ini->wajib_pokok;
        $wajib_jasa = $ra_bulan_ini->wajib_jasa;
        $target_pokok = $ra_bulan_ini->target_pokok;
        $target_jasa = $ra_bulan_ini->target_jasa;
        $angsuran_ke = $ra_bulan_ini->angsuran_ke;
    }

    $jum_angsuran = $pinkel->jangka / $pinkel->sis_pokok->sistem;
    if ($real->saldo_pokok + $real->saldo_jasa <= 0) {
        $angsuran_ke = $jum_angsuran;
    }

    if ($ra->jatuh_tempo <= $real->tgl_transaksi) {
        $angsur_bulan_depan = false;
    }
    $tunggakan_pokok = $target_pokok - $real->sum_pokok;
    if ($tunggakan_pokok < 0) {
        $tunggakan_pokok = 0;
    }
    $tunggakan_jasa = $target_jasa - $real->sum_jasa;
    if ($tunggakan_jasa < 0) {
        $tunggakan_jasa = 0;
    }

    $pokok_bulan_depan = $pinkel->alokasi - $real->sum_pokok;
    $jasa_bulan_depan = ($pinkel->alokasi * $pinkel->pros_jasa) / 100 - $real->sum_jasa;

    if ($pokok_bulan_depan > 0 && $angsuran_ke + 1 <= $jum_angsuran) {
        $pokok_bulan_depan = $wajib_pokok;
    }

    if ($jasa_bulan_depan > 0 && $angsuran_ke + 1 <= $jum_angsuran) {
        $jasa_bulan_depan = $wajib_jasa;
    }

    if ($angsuran_ke >= $jum_angsuran) {
        $pokok_bulan_depan = 0;
        $jasa_bulan_depan = 0;
    }

    if (!$angsur_bulan_depan) {
        $pokok_bulan_depan = 0;
        $jasa_bulan_depan = 0;
    }
    $nama_user = '';

    $no_kuitansi = '';
@endphp
@foreach ($real->trx as $trx)
    @php
        $keterangan .= $trx->keterangan_transaksi . ', ';
        if (
            $trx->rekening_kredit == '4.1.02.01' ||
            $trx->rekening_kredit == '4.1.02.02' ||
            $trx->rekening_kredit == '4.1.02.03' || 
            $trx->rekening_kredit == '4.1.02.04' || 
            $trx->rekening_kredit == '4.1.02.05' 
        ) {
            $denda += $trx->jumlah;
        }

        $no_kuitansi .= $trx->idt . '/';

        $nama_user = $trx->user->namadepan . ' ' . $trx->user->namabelakang;
    @endphp
@endforeach

<!DOCTYPE html>
<html>
<head>
    <title>Struk Angsuran Kelompok {{ $pinkel->kelompok->nama_kelompok }} - {{ $pinkel->id }}</title>
    <style type="text/css">
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            margin: 0;
            padding: 5mm;
            width: 70mm;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .header h3 {
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .header p {
            margin: 2px 0;
            font-size: 9px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .row-label {
            width: 40%;
        }

        .row-value {
            width: 60%;
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            font-size: 8px;
        }

        .signature {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        .signature div {
            text-align: center;
            width: 45%;
        }

        table {
            width: 100%;
            font-size: 10px;
        }

        table td {
            padding: 1px 0;
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="header">
        <h3>{{ strtoupper($kec->nama_lembaga_sort) }}</h3>
        <h3>{{ strtoupper($kec->nama_kec) }}</h3>
        <p>{{ $kec->alamat_kec }}</p>
        <p>Telp. {{ $kec->telpon_kec }}</p>
    </div>

    <div class="center bold" style="margin: 8px 0;">
        BUKTI ANGSURAN KELOMPOK
    </div>

    <table>
        <tr>
            <td width="40%">No. Kuitansi</td>
            <td width="5%">:</td>
            <td width="55%">{{ substr($no_kuitansi, 0, -1) }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ Tanggal::tglIndo($real->tgl_transaksi) }}</td>
        </tr>
        <tr>
            <td>Loan ID</td>
            <td>:</td>
            <td class="bold">{{ $pinkel->id }} - {{ $pinkel->jpp->nama_jpp }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td width="40%">Kode Kelompok</td>
            <td width="5%">:</td>
            <td width="55%">{{ $pinkel->kelompok->kd_kelompok }}</td>
        </tr>
        <tr>
            <td>Nama Kelompok</td>
            <td>:</td>
            <td class="bold">{{ $pinkel->kelompok->nama_kelompok }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $pinkel->kelompok->d->nama_desa }}</td>
        </tr>
        <tr>
            <td>Tgl Cair</td>
            <td>:</td>
            <td>{{ Tanggal::tglIndo($pinkel->tgl_cair) }}</td>
        </tr>
        <tr>
            <td>Angsuran Ke</td>
            <td>:</td>
            <td>{{ ($ra_angsuran_ke && $ra_angsuran_ke->angsuran_ke > 0) ? $ra_angsuran_ke->angsuran_ke : 1 }} dari {{ $jum_angsuran }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td colspan="3" class="bold">STATUS PINJAMAN</td>
        </tr>
        <tr>
            <td width="50%">Alokasi</td>
            <td width="25%" class="right">{{ number_format($pinkel->alokasi) }}</td>
            <td width="25%" class="right">{{ number_format(($pinkel->alokasi * $pinkel->pros_jasa) / 100) }}</td>
        </tr>
        <tr>
            <td>Target s.d. Bln Ini</td>
            <td class="right">{{ number_format($target_pokok) }}</td>
            <td class="right">{{ number_format($target_jasa) }}</td>
        </tr>
        <tr>
            <td>Realisasi</td>
            <td class="right">{{ number_format($real->sum_pokok) }}</td>
            <td class="right">{{ number_format($real->sum_jasa) }}</td>
        </tr>
        <tr>
            <td class="bold">Saldo Pinjaman</td>
            <td class="right bold">{{ number_format($real->saldo_pokok) }}</td>
            <td class="right bold">{{ number_format($real->saldo_jasa) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td colspan="3" class="bold">PEMBAYARAN HARI INI</td>
        </tr>
        <tr>
            <td width="50%">Pokok</td>
            <td width="5%">:</td>
            <td width="45%" class="right">{{ number_format($real->realisasi_pokok) }}</td>
        </tr>
        <tr>
            <td>Jasa</td>
            <td>:</td>
            <td class="right">{{ number_format($real->realisasi_jasa) }}</td>
        </tr>
        <tr>
            <td>Denda</td>
            <td>:</td>
            <td class="right">{{ number_format($denda) }}</td>
        </tr>
        <tr>
            <td class="bold">TOTAL BAYAR</td>
            <td>:</td>
            <td class="right bold">{{ number_format($real->realisasi_pokok + $real->realisasi_jasa + $denda) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div style="font-size: 9px; margin: 5px 0;">
        Terbilang: <span class="bold">{{ strtoupper($keuangan->terbilang($real->realisasi_pokok + $real->realisasi_jasa + $denda)) }} RUPIAH</span>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td colspan="3" class="bold">TAGIHAN BULAN DEPAN</td>
        </tr>
        <tr>
            <td width="50%">Tunggakan</td>
            <td width="25%" class="right">{{ number_format($tunggakan_pokok) }}</td>
            <td width="25%" class="right">{{ number_format($tunggakan_jasa) }}</td>
        </tr>
        <tr>
            <td>Angsuran Bln Depan</td>
            <td class="right">{{ number_format($pokok_bulan_depan) }}</td>
            <td class="right">{{ number_format($jasa_bulan_depan) }}</td>
        </tr>
        <tr>
            <td class="bold">TOTAL TAGIHAN</td>
            <td class="right bold">{{ number_format($tunggakan_pokok + $pokok_bulan_depan) }}</td>
            <td class="right bold">{{ number_format($tunggakan_jasa + $jasa_bulan_depan) }}</td>
        </tr>
    </table>

    <div class="signature">
        <div>
            <p style="margin: 0;">Diterima Oleh,</p>
            <p style="margin: 30px 0 5px 0;">&nbsp;</p>
            <p style="margin: 0; border-top: 1px solid #000; padding-top: 2px;">{{ $nama_user }}</p>
        </div>
        <div>
            <p style="margin: 0;">Penyetor,</p>
            <p style="margin: 30px 0 5px 0;">&nbsp;</p>
            <p style="margin: 0; border-top: 1px solid #000; padding-top: 2px;">{{ $pinkel->kelompok->nama_kelompok }}</p>
        </div>
    </div>

    <div class="footer center">
        <p style="margin: 2px 0;">Dicetak: {{ date('d/m/Y H:i:s') }}</p>
        <p style="margin: 2px 0;">Lembar 1: Kelompok | Lembar 2: Arsip LKM</p>
        <p style="margin: 2px 0;">Bawa kartu angsuran saat mengangsur</p>
        <p style="margin: 2px 0;">{{ $kec->web_kec }}</p>
    </div>

</body>
</html>
