@php
use App\Utils\Keuangan;
$keuangan = new Keuangan();
$section = 0;
$empty = false;
$y12 = date('Y') - 1;
@endphp

@extends('pelaporan.layout.base')

@section('content')
<style type="text/css">
    .style6 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
    }

    .style9 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        -webkit-print-color-adjust: exact;
    }

    .style10 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        -webkit-print-color-adjust: exact;
    }

    .top {
        border-top: 1px solid #000000;
    }

    .bottom {
        border-bottom: 1px solid #000000;
    }

    .left {
        border-left: 1px solid #000000;
    }

    .right {
        border-right: 1px solid #000000;
    }

    .all {
        border: 1px solid #000000;
    }

    .style26 {
        font-family: Arial, Helvetica, sans-serif
    }

    .style27 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        font-weight: bold;
    }

    .align-justify {
        text-align: justify;
    }

    .align-center {
        text-align: center;
    }

    .align-right {
        text-align: right;
    }

</style>

@php
    $jumlah_aktif = 0;

    // Flatten seluruh jenis simpanan menjadi satu daftar, lalu urutkan berdasarkan
    // Tanggal Penyimpanan (tgl_buka) ascending. Kalau loop per jenis simpanan,
    // tanggal akan "reset" di setiap produk.
    $simpanan_semua = collect();
    foreach ($jenis_simpanan as $jenis) {
        foreach ($jenis->simpanan as $simpanan) {
            $simpanan->nama_js = $jenis->nama_js;
            $simpanan_semua->push($simpanan);
        }
    }
    $simpanan_semua = $simpanan_semua->sortBy([
        ['tgl_buka', 'asc'],
        ['id', 'asc'],
    ])->values();

    $nomor = 1;
    $total_saldo = 0;
@endphp

<table width="90%" border="0" align="center" cellpadding="3" cellspacing="0">

    <tr>
        <td height="20" colspan="3" class="bottom">

        </td>
        <td height="20" colspan="3" class="bottom">
         
        </td>
    </tr>

    <tr>
        <td height="20" colspan="6" class="style6 bottom align-center"><br>DAFTAR RINCIAN TABUNGAN <br><br>
        </td>
    </tr>
</table>
<table width="90%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td width="20%" class="style9">NAMA LKM</td>
        <td width="70%" class="style9">:{{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9">SANDI LKM</td>
        <td width="70%" class="style9">:{{ $kec->sandi_lkm }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
        <td width="70%" class="style9 bottom">:{{ $tgl }}</td>
    </tr>
</table>
<table width="90%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr align="center" height="30px" class="style9 ">
        <th width="4%" rowspan="2" class="left bottom">No</th>
        <th width="11%" rowspan="2" class="left bottom">LOAN ID</th>
        <th width="14%" rowspan="2" class="left bottom">NAMA PENYIMPAN</th>
        <th width="9%" rowspan="2" class="left bottom">CIF / NO. ANGGOTA</th>
        <th width="10%" rowspan="2" class="left bottom">NIK</th>
        <th width="10%" rowspan="2" class="left bottom">Jenis Simpanan</th>
        <th width="14%" colspan="2" class="left bottom">Suku Bunga</a></th>
        <th width="16%" rowspan="2" class="left bottom right">Jumlah </a></th>

    </tr>

    <tr align="center" height="30px" class="style9">
        <th width="5%" class="left bottom">%</th>
        <th width="10%" class="left bottom">Keterangan</th>

    </tr>

@foreach ($simpanan_semua as $simp)
    @php
        $jumlah_aktif += 1;
        $tgl_buka = explode('-', $simp->tgl_buka);
        $tgl1 = new DateTime($simp->tgl_tutup);
        $tgl2 = new DateTime($simp->tgl_buka);
        $selisih = $tgl2->diff($tgl1);

        $tgl1 = Tanggal::tglIndo($simp->tgl_tutup);
        $tgl2 = Tanggal::tglIndo($simp->tgl_buka);

        $sum_saldo = 0;
        foreach ($simp->trx as $trx) {
            if ($trx->rekening_kredit == '2.1.04.01' || $trx->rekening_kredit == '2.1.04.01') {
                $sum_saldo -= $trx->jumlah;
            } 
            else {
                $sum_saldo += $trx->jumlah;
            }
        }

        // Tambahkan saldo ke total saldo
        $total_saldo += $sum_saldo;
    @endphp
    <tr align="right" height="15px" class="style9">
        <td class="left top" align="center">{{ $nomor++ }}</td>
        <td class="left top" align="left">{{ $simp->nomor_rekening }}</td>
        <td class="left top" align="left">{{ strtoupper($simp->namadepan) }}</td>
        <td class="left top" align="left">{{ $simp->id }}</td>
        <td class="left top" align="left">{{ $simp->nik }}</td>
        <td class="left top" align="left">{{ strtoupper($simp->nama_js) }}</td>
        <td class="left top" align="center">{{ $simp ? $simp->bunga : '0' }}</td>
        <td width="20%" class="left top" align="left">Per Bulan</td>
        <td class="left top" align="center"style="border: 1px solid;">{{ number_format($sum_saldo, 0, '.', ',') }}</td>
    </tr>
@endforeach

<tr class="style9">
    <th colspan="9" class="left bottom top" align="center" style="background:rgba(0,0,0, 0.3);">JUMLAH SALDO</th>
    <th class="left right bottom top" align="center">{{ number_format($total_saldo, 0, '.', ',') }}</th>
</tr>

    <tr class="style9">
        <th colspan="9" class="bottom" align="center">&nbsp;</th>
        <th class="bottom" align="right">&nbsp;</th>
    </tr>
    <tr>
        <td class="style10 top" colspan="10"><b>Keterangan</b> : Data yang ditampilkan diatas merupakan
            Tabungan pada tahun berjalan {{$tahun}} untuk menampilkan data Individu aktif tahun lalu
            dapat memilih mode tahun lalu {{$y12}}</td>
    </tr>

</table>

@endsection
