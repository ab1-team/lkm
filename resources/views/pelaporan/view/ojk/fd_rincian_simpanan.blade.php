<!DOCTYPE html>
<html>

<head>
    <title>Daftar Rincian Tabungan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .border {
            border: 1px solid black;
        }

        .header {
            font-size: 16px;
            font-weight: bold;
        }

        .ttd {
            margin-top: 50px;
        }

        thead th {
            background-color: #d0d0d0;
            padding: 7px 10px;
            text-transform: capitalize;
            font-size: 11px;
        }

        #tabel-utama tbody td {
            padding: 5px 10px;
        }

        #tabel-utama tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        #tabel-utama tfoot td {
            padding: 5px 10px;
        }

        .row-desa td {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 6px 10px;
        }

    </style>
</head>

<body>
    <table width="95%" align="center">
        <tr>
            <td class="center header">
                Daftar Rincian Tabungan
            </td>
        </tr>
    </table>

    <br>

    <table width="95%" align="center">
        <tr>
            <td width="20%">Nama LKM</td>
            <td width="80%">: {{ $kec->nama_kec }}</td>
        </tr>
        <tr>
            <td>Sandi LKM</td>
            <td>: {{ $kec->sandi_lkm }}</td>
        </tr>
        <tr>
            <td>Periode laporan</td>
            <td>: {{ $sub_judul }}</td>
        </tr>
    </table>

    <br>

    <table id="tabel-utama" width="95%" align="center">
        <thead>
            <tr>
                <th class="border center">No</th>
                <th class="border center">Nama anggota</th>
                <th class="border center">Jenis anggota</th>
                <th class="border center">CIF</th>
                <th class="border center">NIK</th>
                <th class="border center">Jenis simpanan</th>
                <th class="border center">Saldo akhir</th>
                <th class="border center">Tgl buka</th>
                <th class="border center">Tgl tutup</th>
                <th class="border center">Bunga (%)</th>
                <th class="border center">Pajak (%)</th>
                <th class="border center">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $total = 0;
                $desa_sekarang = null;
            @endphp

            @foreach($jenis_simpanan as $jenis)
                @foreach($jenis->simpanan as $simpanan)
                    @php
                        $jumlah = $simpanan->realSimpananTerbesar->sum ?? 0;
                        $total += $jumlah;
                        $nama_desa = ucwords(strtolower($simpanan->nama_desa ?? 'Tanpa Desa'));
                    @endphp

                    {{-- Row desa jika berganti --}}
                    @if($nama_desa !== $desa_sekarang)
                        @php $desa_sekarang = $nama_desa; @endphp
                        <tr class="row-desa">
                            <td colspan="12" class="border">{{ $nama_desa }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="border center">{{ $no++ }}</td>
                        <td class="border">{{ ucwords(strtolower($simpanan->namadepan)) }}</td>
                        <td class="border center">Anggota</td>
                        <td class="border center">{{ $simpanan->id }}</td>
                        <td class="border center">{{ $simpanan->nik }}</td>
                        <td class="border center">{{ $jenis->nama_js }}</td>
                        <td class="border right">{{ number_format($jumlah, 0, '.', ',') }}</td>
                        <td class="border center">
                            {{ date('d-m-Y', strtotime($simpanan->tgl_buka)) }}
                        </td>
                        <td class="border center">
                            {{ $simpanan->tgl_tutup ? date('d-m-Y', strtotime($simpanan->tgl_tutup)) : '-' }}
                        </td>
                        <td class="border center">
                            {{ $simpanan->bunga ?? '0' }}%
                        </td>
                        <td class="border center">
                            {{ $simpanan->pajak ?? '0' }}%
                        </td>
                        <td class="border">
                            {{ $simpanan->keterangan ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="border center"><b>Jumlah</b></td>
                <td class="border right">
                    <b>{{ number_format($total, 0, '.', ',') }}</b>
                </td>
                <td colspan="5" class="border"></td>
            </tr>
        </tfoot>
    </table>

    <br><br>

    <table class="p" border="0" align="center" width="96%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr>
            <td colspan="14">
                <div style="margin-top: 14px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>
    </table>

</body>

</html>
