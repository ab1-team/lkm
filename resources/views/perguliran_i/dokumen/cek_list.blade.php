@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <!-- Judul -->
    <div style="width:100%; font-size:10pt;">
        <div style="text-align:center; font-size:18pt; font-weight:bold; margin-bottom:5px;">
            CHECK LIST
        </div>
        <div style="text-align:center; font-size:16pt; margin-bottom:5px;">
            KELENGKAPAN PROPOSAL {{ strtoupper($pinkel->jpp->nama_jpp) }}
        </div>
    </div>

    <!-- Identitas -->
    <div style="width:100%; font-size:10pt; text-align:justify; margin-bottom:10px;">
        <div style="display:table; width:100%; font-size:10pt; text-align:justify;"></div>
    </div>
    
    <table border="0" width="100%" align="center" cellspacing="0" cellpadding="0"
        style="font-size: 10pt; table-layout: fixed;">
        <tr style="background: rgb(232, 232, 232)">
            <th rowspan="2" class="l t b" height="20" width="6%" align="center">NO</th>
            <th rowspan="2" class="l t b" width="32%" align="center">Nama Dokumen</th>
            <th colspan="3" class="l t b" width="5%" align="center">Ceklis</th>
            <th rowspan="2" class="l t b r" width="32%" align="center">Catatan</th>
        </tr>
        <tr style="background: rgb(232, 232, 232)">
            <th class="l t b" height="20" width="10%" align="center">Cukup</th>
            <th class="l t b" width="10%" align="center">Kurang</th>
            <th class="l t b r" width="10%" align="center">Tidak Ada</th>
        </tr>
        <tr>
            <td class="l t b" align="center">1</th>
            <td class="l t b" align="left">Formulir Pinjaman</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">2</th>
            <td class="l t b" align="left">Formulir Verifikasi</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">3</th>
            <td class="l t b" align="left">Surat Persetujuan</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">4</th>
            <td class="l t b" align="left">Foto Pinjaman</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">5</th>
            <td class="l t b" align="left">Surat Pengantar dari Desa</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">6</th>
            <td class="l t b" align="left">Surat Pernyataan Jaminan</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">7</th>
            <td class="l t b" align="left">Kartu Tanda Penduduk</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">8</th>
            <td class="l t b" align="left">Kartu Keluarga</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">9</th>
            <td class="l t b" align="left">Surat Nikah</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">10</th>
            <td class="l t b" align="left">Jaminan</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">11</th>
            <td class="l t b" align="left">Surat Perjanjian Kredit</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">12</th>
            <td class="l t b" align="left">Kuitansi</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">13</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">14</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">15</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">16</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">17</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">18</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">19</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
        <tr>
            <td class="l t b" align="center">20</th>
            <td class="l t b" align="left">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b" align="center">&nbsp;</th>
            <td class="l t b r" align="center">&nbsp;</th>
        </tr>
    </table>
    <br><br><br>
    <div style="width:100%; font-size:10pt;">
        <div style="width:40%; margin-left:auto; text-align:center;">
            <div>Diperiksa oleh</div>
            <div style="height:80px; vertical-align:bottom;">
                @if ($ttdQrUrl = \App\Utils\QrTtdHelper::inlineDataUri(session('lokasi')))
                    <img src="{{ $ttdQrUrl }}" width="150" height="auto" style="height:auto" alt="{{ $kec->id }}">
                @else
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                @endif
            </div>
            <div>
                <b>
                    @if (\App\Utils\QrTtdHelper::displayHasName(session('lokasi')))
                        {{ $dir->namadepan }} {{ $dir->namabelakang }}
                    @else
                        &nbsp;
                    @endif
                </b>
            </div>
        </div>
    </div>

@endsection
