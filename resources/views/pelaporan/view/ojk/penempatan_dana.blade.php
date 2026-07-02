@php
    use App\Utils\Keuangan;
    $keuangan = new Keuangan();
    $section = 0;
    $empty = false;
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

    .align-left {
        text-align: left;
    }
</style>

    <table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
        <tr>
            <td height="20"class="bottom"></td>
            <td height="20" class="bottom"></td>
        </tr>
        <tr>
            <td height="20" colspan="2" class="style6 bottom align-center"><br>Daftar Rincian Penempatan Dana<br><br></td>
        </tr>
    </table>

    <table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
        <tr>
            <td width="20%" class="style9">NAMA LKM</td>
            <td width="70%" class="style9">: {{ $kec->nama_lembaga_long }}</td>
        </tr>
        <tr>
            <td width="20%" class="style9">SANDI LKM</td>
            <td width="70%" class="style9">: {{ $kec->sandi_lkm }}</td>
        </tr>
        <tr>
            <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
            <td width="70%" class="style9 bottom">: {{ $tgl }}</td>
        </tr>
        <tr>
            <td height="20"class="bottom"></td>
            <td height="20" class="bottom"></td>
        </tr>
    </table>
    
    <table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
        <tr align="center" height="30px" class="style9">
            <th width="5%" class="left bottom">No</th>
            <th width="40%" class="left bottom">Kode Akun</th>
            <th width="26%" class="left bottom">Nama Akun</th>
            <th width="25%" class="left bottom right">Nominal / Saldo</th>
        </tr>
        
        @php
            $nomor = 0;
        @endphp
        @foreach ($rekening as $rek)
            @php
                $nomor++;
                $debit  =$rek->saldo->debit ?? 0;
                $kredit =$rek->saldo->kredit ?? 0;
                $saldo  = $debit - $kredit;
            @endphp



            <tr align="center"class="style9">
                <td class="left bottom">{{$nomor}}</td>
                <td class="left bottom">{{$rek->kode_akun}}</td>
                <td class="left bottom">{{$rek->nama_akun}}</td>
                <td class="left bottom right">{{number_format($saldo, 0, '.', ',')}}</td>
            </tr>
        @endforeach
        
        <tr>
            <td colspan="4">
                <div style="margin-top: 14px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>

    </table>
@endsection
