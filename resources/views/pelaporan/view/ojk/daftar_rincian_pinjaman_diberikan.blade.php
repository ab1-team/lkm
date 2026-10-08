@extends('pelaporan.layout.base')

@section('content')

    @php
        // Flat list kolom a-o. Urutan tgl_cair + id sudah diflatten di
        // DrpPinjamanDiberikan; view ini tidak mengelompokkan per desa
        // maupun per produk pinjaman.
        $rows = $rows ?? collect();
        $gap = $gap ?? ['total' => 0, 'per_kolom' => [], 'baris' => []];
        $total = $total ?? [];

        // Format nominal 1,234,567.89. Nilai di-cast ke float supaya
        // number_format() tidak menolak string.
        $uang = fn ($v) => number_format((float) ($v ?? 0), 2);
        $tglFormat = fn ($v) => $v ? date('Y-m-d', strtotime((string) $v)) : '';
    @endphp

    <style>
        .row-body td,
        .row-body th { font-size: 8px; }
        .uang { white-space: nowrap; }
    </style>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="16" align="center">
                <div style="font-size: 18px;">
                    <b>DAFTAR RINCIAN PINJAMAN YANG DIBERIKAN</b>
                </div>
                <div style="font-size: 13px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
        <tr>
            <td class="t l b" style="width: 18%;">NAMA LKM</td>
            <td class="t l b" style="width: 32%;">{{ $kec->nama_lembaga_long }}</td>
            <td class="t l b" style="width: 18%;">SANDI LKM</td>
            <td class="t l b" style="width: 32%;">{{ $kec->sandi_lkm }}</td>
        </tr>
        <tr>
            <td class="t l b">PERIODE LAPORAN</td>
            <td class="t l b">{{ $tgl }}</td>
            <td class="t l b">POSISI LAPORAN</td>
            <td class="t l b">{{ date('d') }} {{ now()->translatedFormat('F Y') }}</td>
        </tr>
    </table>

    <br>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px; table-layout: fixed;">
        <tr align="center" height="34px">
            <th width="3%"  rowspan="2" class="t l b">No</th>
            <th  rowspan="2" class="t l b">Nama Nasabah Penerima</th>
            <th width="4%"  rowspan="2" class="t l b">Jenis Nasabah</th>
            <th width="9%"  rowspan="2" class="t l b">Nomor Identitas Nasabah</th>
            <th width="4%"  rowspan="2" class="t l b">Jenis Penggu- naan</th>
            <th width="4%"  rowspan="2" class="t l b">Sektor Usaha</th>
            <th width="4%"  rowspan="2" class="t l b">Periode Pemba- yaran</th>
            <th colspan="2" class="t l b">Jangka Waktu</th>
            <th width="4%"  rowspan="2" class="t l b">Suku Bunga</th>
            <th width="8%" rowspan="2" class="t l b">Nilai Pencairan</th>
            <th width="8%" rowspan="2" class="t l b">Saldo Pinjaman</th>
            <th width="8%"  rowspan="2" class="t l b">Tunggakan</th>
            <th width="4%"  rowspan="2" class="t l b">Kualitas</th>
            <th width="4%"  rowspan="2" class="t l b">Jenis Agunan</th>
            <th width="8%"  rowspan="2" class="t l r b">Nilai Agunan</th>
        </tr>
        <tr align="center" height="20px">
            <th width="6%" class="t l b" style="white-space:nowrap;">Mulai</th>
            <th width="6%" class="t l b" style="white-space:nowrap;">Jatuh Tempo</th>
        </tr>

        @foreach ($rows as $row)
            @php
                // Baris dengan gap ditandai supaya reviewer bisa langsung
                // tahu kolom mana yang belum terisi sandi / datanya.
                $adaGap = ! empty($row['gap']);
                $warnaGap = $adaGap ? 'background:rgba(255,255,255,0);' : '';
            @endphp
            <tr class="row-body" style="height: 22px;">
                {{-- a --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['no'] }}</td>
                {{-- b — dua bagian saja: NAMA - ID. ID = simpanan.id atau pinjaman.id. --}}
                <td class="t l b" style="{{ $warnaGap }} mso-number-format:'@';" align="left">{{ $row['nama_lengkap'] }}</td>
                {{-- c --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['jenis_nasabah'] }}</td>
                {{-- d — string, bukan angka: 16 digit NIK tidak boleh dibulatkan --}}
                <td class="t l b" style="{{ $warnaGap }} mso-number-format:'@';" align="left">{{ $row['nomor_identitas'] }}</td>
                {{-- e --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['jenis_penggunaan'] ?? '' }}</td>
                {{-- f --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['sektor_usaha'] ?? '' }}</td>
                {{-- g --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['periode_pembayaran'] }}</td>
                {{-- h --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $tglFormat($row['tgl_mulai']) }}</td>
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $tglFormat($row['tgl_jatuh_tempo']) }}</td>
                {{-- i --}}
                <td class="t l b" style="{{ $warnaGap }}" align="right">{{ number_format((float) $row['suku_bunga'], 2) }}%</td>
                {{-- j --}}
                <td class="t l b uang" style="{{ $warnaGap }}" align="right">{{ $uang($row['nilai_pencairan']) }}</td>
                {{-- k --}}
                <td class="t l b uang" style="{{ $warnaGap }}" align="right">{{ $uang($row['baki_debet']) }}</td>
                {{-- l --}}
                <td class="t l b uang" style="{{ $warnaGap }}" align="right">{{ $uang($row['tunggakan']) }}</td>
                {{-- m --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['kolektibilitas'] ?? $row['kolektibilitas_label'] }}</td>
                {{-- n --}}
                <td class="t l b" style="{{ $warnaGap }}" align="center">{{ $row['jenis_agunan'] ?? '' }}</td>
                {{-- o --}}
                <td class="t l r b uang" style="{{ $warnaGap }}" align="right">{{ $row['nilai_agunan'] === null ? '' : $uang($row['nilai_agunan']) }}</td>
            </tr>
        @endforeach

        @if ($rows->isNotEmpty())
            <tr class="row-body" style="font-weight: bold;">
                {{-- colspan 10: kolom No s/d Suku Bunga (Jangka Waktu = 2 kolom fisik).
                     Total baris ini menutup 16 kolom fisik: 10 + j + k + l + (m,n = 2) + o. --}}
                <th colspan="10" class="t l b" align="right" style="background:rgba(0,0,0,0.3);">TOTAL ({{ $rows->count() }} Pinjaman)</th>
                <th class="t l b uang" align="right" style="background:rgba(0,0,0,0.3);">{{ $uang($total['nilai_pencairan'] ?? 0) }}</th>
                <th class="t l b uang" align="right" style="background:rgba(0,0,0,0.3);">{{ $uang($total['baki_debet'] ?? 0) }}</th>
                <th class="t l b uang" align="right" style="background:rgba(0,0,0,0.3);">{{ $uang($total['tunggakan'] ?? 0) }}</th>
                <th colspan="2" class="t l r b" align="right" style="background:rgba(0,0,0,0.3);"></th>
                <th class="t l r b uang" align="right" style="background:rgba(0,0,0,0.3);">{{ $uang($total['nilai_agunan'] ?? 0) }}</th>
            </tr>
        @endif
    </table>

    @if ($rows->isEmpty())
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="t l b r" align="center" colspan="16" style="font-size: 11px; padding: 12px 0;">
                    Tidak ada pinjaman aktif pada tanggal laporan ini.
                </td>
            </tr>
        </table>
    @endif

    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="16">
                <div style="margin-top: 16px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>
    </table>

@endsection
