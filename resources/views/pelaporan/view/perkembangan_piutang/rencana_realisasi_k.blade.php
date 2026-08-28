@php
    use App\Utils\Tanggal;
    $keuangan = new Keuangan();

    $section = 0;
    $empty = false;
@endphp
<title>{{ $title }}</title>
@extends('pelaporan.layout.base')

@section('content')
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;

            body {
                font-family: 'DejaVu Sans', sans-serif;
            }
        }
    </style>
    @php
        $nomor = 0;
    @endphp
    @foreach ($jenis_pp as $jpp)
        @php
            if ($jpp->pinjaman_kelompok->isEmpty()) {
                continue;
            }
            $nomor++;
        @endphp

        @php
            $kd_desa = [];

            $t_kelompok = 0;
            $t_pemanfaat = 0;
            $t_pengajuan = 0;
            $t_pencairan = 0;
        @endphp

        @if ($nomor > 1)
            <div class="break"></div>
            @php
                $empty = false;
            @endphp
        @endif

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>LAPORAN REALISASI PENCAIRAN KELOMPOK {{ $jpp->nama_jpp }}</b>
                    </div>
                    <div style="font-size: 16px;">
                        <b style="text-transform: uppercase;">
                            {{ strtoupper($sub_judul) }}
                        </b>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="5"></td>
            </tr>
        </table>

        <table border="0" width="100%" cellspacing="0" cellpadding="0"
            style="font-size: 11px; table-layout: fixed; word-wrap: break-word;">
            <tr>
                <th class="t l b" rowspan="2" width="4%">No</th>
                <th class="t l b" rowspan="2" width="26%">Nama Kelompok - Load ID</th>
                <th class="t l b" rowspan="2" width="26%">Alamat</th>
                <th class="t l b" rowspan="2" width="15%">Ketua</th>
                <th class="t l b" rowspan="2" width="8%">jumlah</th>

                <th class="t l b" rowspan="2" width="18%">Nomor SPK</th>
                <th class="t l b" rowspan="2" width="12%">Tgl Cair</th>
                <th class="t l b" rowspan="2" width="6%">T/S</th>
                <th class="t l b r" colspan="2" width="28%">Alokasi</th>
            </tr>
            <tr>
                <th class="t l b" width="11%">Pengajuan</th>
                <th class="t l b r" width="11%">Pencairan</th>
            </tr>

            @foreach ($jpp->pinjaman_kelompok as $pinkel)
                @php
                    $kd_desa[] = $pinkel->kd_desa;
                    $desa = $pinkel->kd_desa;
                @endphp

                @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1')
                    @if ($section != $desa && count($kd_desa) > 1)
                        @php
                            $t_kelompok += $j_kelompok;
                            $t_pemanfaat += $j_pemanfaat;
                            $t_pengajuan += $j_pengajuan;
                            $t_pencairan += $j_pencairan;
                        @endphp
                        <tr style="font-weight: bold;">
                            <td class="t l b" colspan="8" align="left" height="15">
                                Jumlah pemanfaat {{ $nama_desa }} ({{ $j_kelompok }})
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($j_pengajuan, 2) }}
                            </td>
                            <td class="t l b r" align="right">
                                {{ number_format($j_pencairan, 2) }}
                            </td>
                        </tr>
                    @endif

                    <tr style="font-weight: bold;">
                        <td class="t l b r" colspan="10" align="left">
                            {{ $pinkel->kode_desa }}. {{ $pinkel->nama_desa }}
                        </td>
                    </tr>

                    @php
                        $nomor = 1;

                        $section = $pinkel->kd_desa;
                        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
                        $j_kelompok = 0;
                        $j_pemanfaat = 0;
                        $j_pengajuan = 0;
                        $j_pencairan = 0;
                    @endphp
                @endif

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b">
                        {{ $pinkel->nama_kelompok }} - {{ $pinkel->id }}
                    </td>
                    <td class="t l b">
                        <span style="font-size: 9px;">{{ $pinkel->nama_desa }}</span>
                    </td>
                    <td class="t l b">
                        <span style="font-size: 9px;">{{ $pinkel->ketua }}</span>
                    </td>
                    <td class="t l b" align="center">
                        {{ $pinkel->pinjaman_anggota_count }}
                    </td>
                    <td class="t l b">{{ $pinkel->spk_no }}</td>
                    <td class="t l b" align="center">{{ Tanggal::tglIndo($pinkel->tgl_cair) }}</td>
                    <td class="t l b" align="center">{{ $pinkel->jangka }}/{{ $pinkel->sis_pokok->sistem }}</td>
                    <td class="t l b" align="right">{{ number_format($pinkel->proposal, 2) }}</td>
                    <td class="t l b r" align="right">{{ number_format($pinkel->alokasi, 2) }}</td>
                </tr>

                @php
                    $j_kelompok += 1;
                    $j_pemanfaat += $pinkel->pinjaman_anggota_count;
                    $j_pengajuan += $pinkel->proposal;
                    $j_pencairan += $pinkel->alokasi;
                @endphp
            @endforeach

            @if (count($kd_desa) > 0)
                @php
                    $t_kelompok += $j_kelompok;
                    $t_pemanfaat += $j_pemanfaat;
                    $t_pengajuan += $j_pengajuan;
                    $t_pencairan += $j_pencairan;
                @endphp

                <tr style="font-weight: bold;">
                    <td class="t l b" colspan="8" align="left" height="15">
                        Jumlah Kelompok {{ $nama_desa }} ({{ $j_kelompok }})
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($j_pengajuan, 2) }}
                    </td>
                    <td class="t l b r" align="right">
                        {{ number_format($j_pencairan, 2) }}
                    </td>
                </tr>

                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td class="t l b" colspan="8" align="center" height="15">
                        J U M L A H ({{ $t_kelompok }})
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($t_pengajuan, 2) }}
                    </td>
                    <td class="t l b r" align="right">
                        {{ number_format($t_pencairan, 2) }}
                    </td>
                </tr>
            @endif
        </table>
    @endforeach
@endsection
