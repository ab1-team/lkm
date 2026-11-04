@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
        <tr class="b">
            <td colspan="3" align="center">
                <div style="font-size: 18pt;">
                    <b>Susunan Pengurus</b>
                </div>
                <div style="font-size: 16pt;">
                    <b>Kelompok {{ $pinkel->kelompok->nama_kelompok }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
        <tr>
            <td width="40">Kode Kelompok</td>
            <td width="5" align="right">:</td>
            <td width="150">
                <b>{{ $pinkel->kelompok->kd_kelompok }}</b>
            </td>
            <td width="40">Tanggal</td>
            <td width="5" align="right">:</td>
            <td width="150">
                <b>{{ Tanggal::tglIndo($pinkel->tgl_proposal) }}</b>
            </td>
        </tr>
        <tr>
            <td>Nama Kelompok</td>
            <td align="right">:</td>
            <td>
                <b>{{ $pinkel->kelompok->nama_kelompok }}</b>
            </td>
            <td>Koordinator</td>
            <td align="right">:</td>
            <td>
                <b>{{ $pinkel->kelompok->ketua }}</b>
            </td>
        </tr>
        <tr>
            <td>Desa/Kelurahan</td>
            <td align="right">:</td>
            <td>
                <b>{{ $pinkel->kelompok->d->nama_desa }}</b>
            </td>
            <td>Telepon</td>
            <td align="right">:</td>
            <td>
                <b>{{ $pinkel->kelompok->telpon }}</b>
            </td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt; margin-top: 11pt;">
        <tr style="background: rgb(232, 232, 232)">
            <th class="l t b" height="16" width="10" align="center">No</th>
            <th class="l t b" width="150" align="center">Jabatan</th>
            <th class="l t b r" width="150" align="center">Nama</th>
        </tr>
        <tr>
            <td class="l t b" height="14" align="center">1.</td>
            <td class="l t b">Koordinator Kelompok</td>
            <td class="l t b r">{{ $pinkel->kelompok->ketua }}</td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td align="center">{{ $kec->nama_kec }}, {{ Tanggal::tglLatin($pinkel->tgl_proposal) }}</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td align="center">Mengetahui,</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td align="center">
                {{ $pinkel->kelompok->d->sebutan_desa->sebutan_kades }} {{ $pinkel->kelompok->d->nama_desa }}
            </td>
        </tr>
        <tr>
            <td colspan="3" height="30">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td align="center">
                <b>
                    <u>{{ $pinkel->kelompok->d->kades }}</u>
                </b>
            </td>
        </tr>
    </table>
@endsection
