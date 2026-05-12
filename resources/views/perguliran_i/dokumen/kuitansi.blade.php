@php
    use App\Utils\Tanggal;

    $waktu = date('H:i');
    $tempat = 'Kantor LKM';

    $wt_cair = explode('_', $pinkel->wt_cair);
    if (count($wt_cair) == 1) {
        $waktu = $wt_cair[0];
    }

    if (count($wt_cair) == 2) {
        $waktu = $wt_cair[0];
        $tempat = $wt_cair[1];
    }
@endphp

@extends('perguliran_i.dokumen.layout.base')
<br><br>
< @section('content') < <div style="padding: 60pt; padding-top: 0pt; border: 1pt solid #000; height: 82%;">
        <table border="0" width="100%" class="p">
            <tr>
                <td colspan="3" height="40" align="center" style="text-transform: uppercase; font-size: 16pt;">
                    <b> K u i t a n s i </b>
                </td>
            </tr>
            <tr>
                <td width="90">Telah Diterima Dari</td>
                <td width="10" align="center">:</td>
                <td class="b">
                    {{ $kec->sebutan_level_3 }} {{ $kec->nama_lembaga_sort }}
                </td>
            </tr>
            <tr>
                <td>Uang Sebanyak</td>
                <td align="center">:</td>
                <td class="b">
                    {{ $keuangan->terbilang($pinkel->alokasi) }} Rupiah
                </td>
            </tr>
            <tr>
                <td>Untuk Pembayaran</td>
                <td align="center">:</td>
                <td class="b">
                    Pencairan Pinjaman Individu Atas Nama {{ $pinkel->anggota->namadepan }}
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="b">
                    Beralamat Di {{ $pinkel->anggota->alamat }}
                    {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}
                    {{ $pinkel->anggota->d->nama_desa }}
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="b">
                    Loan ID. {{ $pinkel->id }} &mdash; SPK No. {{ $pinkel->spk_no }}
                </td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" class="t b" align="center">
                    Rp. {{ number_format($pinkel->alokasi) }}
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>

        <table border="0" width="100%" style="font-size: 10pt;">
    <tr>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="6">&nbsp;</td>
        <td colspan="3" align="center">
            {{ $kec->nama_kec }}, {{ Tanggal::tglLatin($pinkel->tgl_cair) }}
        </td>
    </tr>

    
    @if (in_array(session('lokasi'), [1, 351, 352, 353, 354]))
    <tr>
        <td align="center" colspan="6">Setuju Dibayarkan</td>
        <td align="center" colspan="6">Diterima Oleh</td>
    </tr>
    <tr>
        <td align="center" colspan="6">{{ $kec->sebutan_level_1 }}</td>
        <td align="center" colspan="6"></td>
    </tr>
    <tr>
        <td align="center" colspan="6">
            @php
                $qrDirPath = storage_path('app/public/qr/' . session('lokasi') . '.jpeg');
            @endphp

            @if (file_exists($qrDirPath))
                <img src="../storage/app/public/qr/{{ session('lokasi') }}.jpeg" height="70" alt="{{ $kec->id }}">
            @else
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
            @endif
        </td>
        <td align="center" colspan="6">
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="6">
            <b>{{ $dir->namadepan }} {{ $dir->namabelakang }}</b>
        </td>
        <td align="center" colspan="6">
            <b>{{ $pinkel->anggota->namadepan }}</b>
        </td>
    </tr>
    @else
    <tr>
        <td align="center" colspan="3">Setuju Dibayarkan</td>
        <td align="center" colspan="3">Dikeluarkan Oleh</td>
        <td align="center" colspan="3">Diterima Oleh</td>
    </tr>
    <tr>
        <td align="center" colspan="3">{{ $kec->sebutan_level_1 }}</td>
        <td align="center" colspan="3">{{ $kec->sebutan_level_3 }}</td>
        <td align="center" colspan="3"></td>
    </tr>
    <tr>
        <td align="center" colspan="3">
            @php
                $qrDirPath = storage_path('app/public/qr/' . session('lokasi') . '.jpeg');
            @endphp

            @if (file_exists($qrDirPath))
                <img src="../storage/app/public/qr/{{ session('lokasi') }}.jpeg" height="70" alt="{{ $kec->id }}">
            @else
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
            @endif
        </td>
        <td align="center" colspan="3">
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
        </td>
        <td align="center" colspan="3">
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="3">
            <b>{{ $dir->namadepan }} {{ $dir->namabelakang }}</b>
        </td>
        <td align="center" colspan="3">
            <b>{{ $bend->namadepan }} {{ $bend->namabelakang }}</b>
        </td>
        <td align="center" colspan="3">
            <b>{{ $pinkel->anggota->namadepan }}</b>
        </td>
    </tr>
    @endif
</table>

        </div>
    @endsection
