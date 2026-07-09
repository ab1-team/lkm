@php
    use App\Utils\Tanggal;
    $keuangan = new Keuangan();

    $section = 0;
    $empty = false;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>
    @php
        $nomor = 0;
    @endphp
    @foreach ($jenis_pp as $jpp)
        @php
            if ($jpp->pinjaman_anggota->isEmpty()) {
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
                        <b>LAPORAN REALISASI PENCAIRAN {{ $jpp->nama_jpp }}</b>
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

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <th class="t l b" rowspan="2" width="3%">No</th>
                <th class="t l b" rowspan="2" width="15%">NIK</th>
                <th class="t l b" rowspan="2" width="30%">Nama Nasabah - Load ID</th>
                <th class="t l b" rowspan="2" width="25%">Alamat</th>
                <th class="t l b" rowspan="2" width="25%">Tempat Lahir</th>
                <th class="t l b" rowspan="2" width="15%">Tanggal Lahir</th>

                <th class="t l b" rowspan="2" width="20%">Nomor SPK</th>
                <th class="t l b" rowspan="2" width="6%">Tgl Cair</th>
                <th class="t l b" rowspan="2" width="4%">T/S</th>
                <th class="t l b r" colspan="2" width="20%">Alokasi</th>
            </tr>
            <tr>
                <th class="t l b" width="11%">Pengajuan</th>
                <th class="t l b r" width="11%">Pencairan</th>
            </tr>

            @foreach ($jpp->pinjaman_anggota as $pinj_i)
                @php
                    $kd_desa[] = $pinj_i->kd_desa;
                    $desa = $pinj_i->kd_desa;
                @endphp

                @if (array_count_values($kd_desa)[$pinj_i->kd_desa] <= '1')
                    @if ($section != $desa && count($kd_desa) > 1)
                        @php
                            $t_kelompok += $j_kelompok;
                            $t_pemanfaat += $j_pemanfaat;
                            $t_pengajuan += $j_pengajuan;
                            $t_pencairan += $j_pencairan;
                        @endphp
                        <tr style="font-weight: bold;">
                            <td class="t l b" colspan="9" align="left" height="15">
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
                        <td class="t l b r" colspan="11" align="left">
                            {{ $pinj_i->kode_desa }}. {{ $pinj_i->nama_desa }}
                        </td>
                    </tr>

                    @php
                        $nomor = 1;

                        $section = $pinj_i->kd_desa;
                        $nama_desa = $pinj_i->sebutan_desa . ' ' . $pinj_i->nama_desa;
                        $j_kelompok = 0;
                        $j_pemanfaat = 0;
                        $j_pengajuan = 0;
                        $j_pencairan = 0;
                    @endphp
                @endif

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b">{{ $pinj_i->nik }}</td>
                    <td class="t l b">
                        {{ $pinj_i->namadepan }} - {{ $pinj_i->id }}
                    </td>
                    <td class="t l b">
                        <span style="font-size: 9px;">{{ $pinj_i->alamat }}</span>
                    </td>
                    <td class="t l b">
                        <span style="font-size: 9px;">{{ $pinj_i->tempat_lahir }}</span>
                    </td>
                    <td class="t l b" align="center">
                        <span style="font-size: 9px;">{{ Tanggal::tglIndo($pinj_i->tgl_lahir) }}</span>
                    </td>
                    <td class="t l b">{{ $pinj_i->spk_no }}</td>
                    <td class="t l b" align="center">{{ Tanggal::tglIndo($pinj_i->tgl_cair) }}</td>
                    <td class="t l b" align="center">{{ $pinj_i->jangka }}/{{ $pinj_i->sis_pokok->sistem }}</td>
                    <td class="t l b" align="right">{{ number_format($pinj_i->proposal, 2) }}</td>
                    <td class="t l b r" align="right">{{ number_format($pinj_i->alokasi, 2) }}</td>
                </tr>

                @php
                    $j_kelompok += 1;
                    $j_pemanfaat += $pinj_i->pinjaman_anggota_count;
                    $j_pengajuan += $pinj_i->proposal;
                    $j_pencairan += $pinj_i->alokasi;
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
                    <td class="t l b" colspan="9" align="left" height="15">
                        Jumlah Kelompok {{ $nama_desa }} ({{ $j_kelompok }})
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($j_pengajuan, 2) }}
                    </td>
                    <td class="t l b r" align="right">
                        {{ number_format($j_pencairan, 2) }}
                    </td>
                </tr>

                <tr>
                    <td colspan="11" style="padding: 0px !important;">
                        <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0"
                            style="font-size: 11px; table-layout: fixed;">
                            <tr style="font-weight: bold;">
                                <td class="t l b" align="center" height="15" width="85%">
                                    J U M L A H ({{ $t_kelompok }})
                                </td>
                                <td class="t l b" align="right" width="8.5%">
                                    {{ number_format($t_pengajuan, 2) }}
                                </td>
                                <td class="t l b r" align="right" width="8.5%">
                                    {{ number_format($t_pencairan, 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td colspan="3">
                                    <div style="margin-top: 16px;"></div>
                                    {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endif
        </table>
    @endforeach
@endsection
