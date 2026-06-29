@php
    use App\Utils\Tanggal;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>{{ strtoupper($laporan ?? 'LAPORAN') }}</b>
                </div>
                <div style="font-size: 16px;">
                    <b>{{ strtoupper($sub_judul ?? '') }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="10"></td>
        </tr>
        <tr>
            <td colspan="3" align="center" style="padding: 40px 0;">
                <div style="font-size: 14px; color: #555;">
                    {{ $pesan ?? 'Tidak ada data untuk laporan ini.' }}
                </div>
            </td>
        </tr>
    </table>
@endsection