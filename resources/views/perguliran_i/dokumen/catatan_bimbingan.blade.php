@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <style>
        .break {
            page-break-after: always;
        }

        p {
            padding: 0;
            margin: 0;
        }
    </style>
    @foreach ($catatan as $ct)
        @if ($loop->iteration > 1)
            <div class="break"></div>
        @endif

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>CATATAN BIMBINGAN PINJAMAN ANGGOTA</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="5"></td>
            </tr>
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td width="22%">Nama Anggota</td>
                <td width="2%" align="center">:</td>
                <td width="25%">{{ $pinkel->anggota->namadepan }} - {{ $pinkel->id }}</td>

                <td width="2%">&nbsp;</td>

                <td width="22%">Pengajuan</td>
                <td width="2%" align="center">:</td>
                <td width="25%">Rp. {{ number_format($pinkel->proposal) }}</td>
            </tr>
            <tr>
                <td>NIK</td>
                <td align="center">:</td>
                <td>{{ $pinkel->anggota->nik }}</td>

                <td>&nbsp;</td>

                <td>Pencairan</td>
                <td align="center">:</td>
                <td>Rp. {{ number_format($pinkel->alokasi) }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td align="center">:</td>
                <td>{{ $pinkel->anggota->alamat }}</td>

                <td>&nbsp;</td>

                <td>Tanggal Cair</td>
                <td align="center">:</td>
                <td>{{ Tanggal::tglLatin($pinkel->tgl_cair) }}</td>
            </tr>
            <tr>
                <td>No. HP</td>
                <td align="center">:</td>
                <td>{{ $pinkel->anggota->hp }}</td>

                <td>&nbsp;</td>

                <td>Nomor SPK</td>
                <td align="center">:</td>
                <td>{{ $pinkel->spk_no }}</td>
            </tr>
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td width="22%">Hasil Catatan Bimbingan</td>
                <td width="2%" align="center">:</td>
                <td width="26%">&nbsp;</td>
                <td width="50%">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="4">
                    {!! $ct['catatan'] !!}
                </td>
            </tr>

            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>

            <tr>
                <td colspan="3">&nbsp;</td>
                <td align="center">{{ $kec->nama_kec }}, {{ Tanggal::tglLatin($ct['tanggal']) }}</td>
            </tr>
            <tr>
                <td colspan="4" height="40">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td align="center">{{ $users[$ct['user']] }}</td>
            </tr>
        </table>
    @endforeach
@endsection
