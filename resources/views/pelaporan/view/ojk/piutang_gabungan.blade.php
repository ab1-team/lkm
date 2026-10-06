@php
    use App\Utils\Tanggal;
    $pinkel_exists = isset($pinkel_gabungan) && $pinkel_gabungan->count() > 0;
    $pinj_i_exists = isset($pinj_i_gabungan) && $pinj_i_gabungan->count() > 0;
    $has_any = $pinkel_exists || $pinj_i_exists;
    $ringkasan = [];
    $grand_alokasi = 0;
    $grand_target_pokok = 0;
    $grand_target_jasa = 0;
    $grand_real_bi_pokok = 0;
    $grand_real_bi_jasa = 0;
    $grand_saldo_pokok = 0;
    $grand_tunggakan_pokok = 0;
    $grand_tunggakan_jasa = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>

    @if (!$has_any)
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="20" align="center">
                    <div style="font-size: 18px;">
                        <b>DAFTAR PERKEMBANGAN PIUTANG</b>
                    </div>
                    <div style="font-size: 16px;">
                        <b>{{ strtoupper($sub_judul) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="20"></td>
            </tr>
            <tr>
                <td colspan="3" align="center" style="padding: 40px 0;">
                    <div style="font-size: 14px; color: #555;">
                        Tidak ada data piutang untuk laporan ini.
                    </div>
                </td>
            </tr>
        </table>
    @else
        @if ($pinj_i_exists)
            @php
                $t_alokasi = 0;
                $t_target_pokok = 0;
                $t_target_jasa = 0;
                $t_real_bl_pokok = 0;
                $t_real_bl_jasa = 0;
                $t_real_pokok = 0;
                $t_real_jasa = 0;
                $t_real_bi_pokok = 0;
                $t_real_bi_jasa = 0;
                $t_saldo_pokok = 0;
                $t_tunggakan_pokok = 0;
                $t_tunggakan_jasa = 0;
                $nomor = 1;
                $j_alokasi = 0;
                $j_target_pokok = 0;
                $j_target_jasa = 0;
                $j_real_bl_pokok = 0;
                $j_real_bl_jasa = 0;
                $j_real_pokok = 0;
                $j_real_jasa = 0;
                $j_real_bi_pokok = 0;
                $j_real_bi_jasa = 0;
                $j_saldo_pokok = 0;
                $j_tunggakan_pokok = 0;
                $j_tunggakan_jasa = 0;
                $j_pross = 1;
                $nama_agent = '';
            @endphp

            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr>
                    <td colspan="20" align="center">
                        <div style="font-size: 18px;">
                            <b>DAFTAR PERKEMBANGAN PIUTANG INDIVIDU</b>
                        </div>
                        <div style="font-size: 16px;">
                            <b>{{ strtoupper($sub_judul) }}</b>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="20" height="5"></td>
                </tr>
            </table>

            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 8px; table-layout: fixed;">
                <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                    <th class="t l b" rowspan="2" width="2%">No</th>
                    <th class="t l b" rowspan="2" width="6%">LOAN ID</th>
                    <th class="t l b" rowspan="2" width="8%">NAMA DEBITUR</th>
                    <th class="t l b" rowspan="2" width="7%">CIF / NO. ANGGOTA</th>
                    <th class="t l b" rowspan="2" width="6%">NIK</th>
                    <th class="t l b" rowspan="2" width="4%">
                        <div>Tgl Cair</div>
                        <div><small>(dd/mm/yy)</small></div>
                    </th>
                    <th class="t l b" rowspan="2" width="3%">Bln</th>
                    <th class="t l b" rowspan="2" width="6%">Alokasi</th>
                    <th class="t l b" colspan="2">Target</th>
                    <th class="t l b" colspan="2">Real s.d. Bulan Lalu</th>
                    <th class="t l b" colspan="2">Real Bulan Ini</th>
                    <th class="t l b" colspan="2">Real s.d. Bulan Ini</th>
                    <th class="t l b" rowspan="2">Saldo</th>
                    <th class="t l b" rowspan="2" width="2%">%</th>
                    <th class="t l b r" colspan="2">Tunggakan</th>
                </tr>
                <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b r" width="6%">Jasa</th>
                </tr>

                @foreach ($pinj_i_gabungan as $pinj_i)
                    @php
                        $nomor++;
                    @endphp

                    @php
                        $saldo_pokok = $pinj_i->alokasi;
                        $saldo_jasa = $pinj_i->pros_jasa == 0 ? 0 : $pinj_i->alokasi * ($pinj_i->pros_jasa / 100);
                        $real_pokok = 0;
                        $real_jasa = 0;
                        $sum_pokok = 0;
                        $sum_jasa = 0;
                        if ($pinj_i->saldo) {
                            $real_pokok = $pinj_i->saldo->realisasi_pokok;
                            $real_jasa = $pinj_i->saldo->realisasi_jasa;
                            $sum_pokok = $pinj_i->saldo->sum_pokok;
                            $sum_jasa = $pinj_i->saldo->sum_jasa;
                            $saldo_pokok = $pinj_i->saldo->saldo_pokok;
                            $saldo_jasa = $pinj_i->saldo->saldo_jasa;
                        }
                        if ($saldo_jasa < 0) {
                            $saldo_jasa = 0;
                        }
                        if ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'L') {
                            $saldo_jasa = 0;
                        }

                        $target_pokok = 0;
                        $target_jasa = 0;
                        if ($pinj_i->target) {
                            $target_pokok = $pinj_i->target->target_pokok;
                            $target_jasa = $pinj_i->target->target_jasa;
                        }

                        $tunggakan_pokok = $target_pokok - $sum_pokok;
                        if ($tunggakan_pokok < 0) {
                            $tunggakan_pokok = 0;
                        }
                        $tunggakan_jasa = $target_jasa - $sum_jasa;
                        if ($tunggakan_jasa < 0) {
                            $tunggakan_jasa = 0;
                        }

                        $pross = 1;
                        if ($target_pokok != 0) {
                            $pross = $sum_pokok / $target_pokok;
                        }

                        if ($pinj_i->tgl_lunas <= $tgl_kondisi && in_array($pinj_i->status, ['L', 'R', 'H'])) {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                        }

                        $pros_jasa = $pinj_i->pros_jasa == 0 ? 0 : $pinj_i->pros_jasa / $pinj_i->jangka;
                    @endphp

                    <tr>
                        <td class="t l b" align="center">{{ $nomor }}</td>
                        <td class="t l b" align="left">{{ $pinj_i->id }}</td>
                        <td class="t l b" align="left">{{ strtoupper($pinj_i->namadepan) }}</td>
                        <td class="t l b" align="left">{{ $pinj_i->nia }}</td>
                        <td class="t l b" align="left">{{ $pinj_i->nik }}</td>
                        <td class="t l b" align="center">{{ Tanggal::tglIndo($pinj_i->tgl_cair, 'DD/MM/YY') }}</td>
                        <td class="t l b" align="center">
                            <small>{{ $pinj_i->jangka }}*{{ number_format($pros_jasa, 2) }}</small>
                        </td>
                        <td class="t l b" align="right">{{ number_format($pinj_i->alokasi) }}</td>
                        <td class="t l b" align="right">{{ number_format($target_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($target_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_pokok - $pinj_i->real_i_sum_realisasi_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_jasa - $pinj_i->real_i_sum_realisasi_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($pinj_i->real_i_sum_realisasi_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($pinj_i->real_i_sum_realisasi_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                        <td class="t l b" align="center">{{ number_format(floor($pross * 100)) }}</td>

                        @if ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'L')
                            <td class="t l b r" colspan="2" align="center">V-LUNAS {{ Tanggal::tglIndo($pinj_i->tgl_lunas) }}</td>
                        @elseif ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'R')
                            <td class="t l b r" colspan="2" align="center">Rescedulling {{ Tanggal::tglIndo($pinj_i->tgl_lunas) }}</td>
                        @elseif ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'H')
                            <td class="t l b r" colspan="2" align="center">Penghapusan {{ Tanggal::tglIndo($pinj_i->tgl_lunas) }}</td>
                        @else
                            <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                            <td class="t l b r" align="right">{{ number_format($tunggakan_jasa) }}</td>
                        @endif
                    </tr>

                    @php
                        $j_alokasi += $pinj_i->alokasi;
                        $j_target_pokok += $target_pokok;
                        $j_target_jasa += $target_jasa;
                        $j_real_bl_pokok += $sum_pokok - $pinj_i->real_i_sum_realisasi_pokok;
                        $j_real_bl_jasa += $sum_jasa - $pinj_i->real_i_sum_realisasi_jasa;
                        $j_real_pokok += $pinj_i->real_i_sum_realisasi_pokok;
                        $j_real_jasa += $pinj_i->real_i_sum_realisasi_jasa;
                        $j_real_bi_pokok += $sum_pokok;
                        $j_real_bi_jasa += $sum_jasa;
                        $j_saldo_pokok += $saldo_pokok;
                        $j_tunggakan_pokok += $tunggakan_pokok;
                        $j_tunggakan_jasa += $tunggakan_jasa;
                    @endphp
                @endforeach

                @php
                    $t_alokasi += $j_alokasi;
                    $t_target_pokok += $j_target_pokok;
                    $t_target_jasa += $j_target_jasa;
                    $t_real_bl_pokok += $j_real_bl_pokok;
                    $t_real_bl_jasa += $j_real_bl_jasa;
                    $t_real_pokok += $j_real_pokok;
                    $t_real_jasa += $j_real_jasa;
                    $t_real_bi_pokok += $j_real_bi_pokok;
                    $t_real_bi_jasa += $j_real_bi_jasa;
                    $t_saldo_pokok += $j_saldo_pokok;
                    $t_tunggakan_pokok += $j_tunggakan_pokok;
                    $t_tunggakan_jasa += $j_tunggakan_jasa;

                    $j_pross = 1;
                    if ($j_target_pokok != 0) {
                        $j_pross = $j_real_bi_pokok / $j_target_pokok;
                    }
                @endphp
                @if (count($pinj_i_gabungan) > 0)
                @php
                    $t_pross = 1;
                    if ($t_target_pokok != 0) {
                        $t_pross = $t_real_bi_pokok / $t_target_pokok;
                    }
                @endphp
                <tr style="font-weight: bold; background: rgb(230, 230, 230);">
                    <td class="t l b" align="center" colspan="7" height="15">TOTAL PINJAMAN INDIVIDU</td>
                    <td class="t l b" align="right">{{ number_format($t_alokasi) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_target_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_target_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bl_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bl_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bi_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bi_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_saldo_pokok) }}</td>
                    <td class="t l b" align="center">{{ number_format(floor($t_pross * 100)) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_tunggakan_pokok) }}</td>
                    <td class="t l b r" align="right">{{ number_format($t_tunggakan_jasa) }}</td>
                </tr>
                @endif
            </table>

            @php
                $ringkasan[] = [
                    'jenis' => 'Individu',
                    'alokasi' => $t_alokasi,
                    'target_pokok' => $t_target_pokok,
                    'target_jasa' => $t_target_jasa,
                    'real_bi_pokok' => $t_real_bi_pokok,
                    'real_bi_jasa' => $t_real_bi_jasa,
                    'saldo_pokok' => $t_saldo_pokok,
                    'tunggakan_pokok' => $t_tunggakan_pokok,
                    'tunggakan_jasa' => $t_tunggakan_jasa,
                ];
                $grand_alokasi += $t_alokasi;
                $grand_target_pokok += $t_target_pokok;
                $grand_target_jasa += $t_target_jasa;
                $grand_real_bi_pokok += $t_real_bi_pokok;
                $grand_real_bi_jasa += $t_real_bi_jasa;
                $grand_saldo_pokok += $t_saldo_pokok;
                $grand_tunggakan_pokok += $t_tunggakan_pokok;
                $grand_tunggakan_jasa += $t_tunggakan_jasa;
            @endphp
        @endif

        @if ($pinkel_exists && $pinj_i_exists)
            <div style="page-break-after: always;"></div>
        @endif

        @if ($pinkel_exists)
            @php
                $t_alokasi = 0;
                $t_target_pokok = 0;
                $t_target_jasa = 0;
                $t_real_bl_pokok = 0;
                $t_real_bl_jasa = 0;
                $t_real_pokok = 0;
                $t_real_jasa = 0;
                $t_real_bi_pokok = 0;
                $t_real_bi_jasa = 0;
                $t_saldo_pokok = 0;
                $t_tunggakan_pokok = 0;
                $t_tunggakan_jasa = 0;
                $nomor = 1;
                $j_alokasi = 0;
                $j_target_pokok = 0;
                $j_target_jasa = 0;
                $j_real_bl_pokok = 0;
                $j_real_bl_jasa = 0;
                $j_real_pokok = 0;
                $j_real_jasa = 0;
                $j_real_bi_pokok = 0;
                $j_real_bi_jasa = 0;
                $j_saldo_pokok = 0;
                $j_tunggakan_pokok = 0;
                $j_tunggakan_jasa = 0;
                $j_pross = 1;
            @endphp

            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr>
                    <td colspan="20" align="center">
                        <div style="font-size: 18px;">
                            <b>DAFTAR PERKEMBANGAN PIUTANG KELOMPOK</b>
                        </div>
                        <div style="font-size: 16px;">
                            <b>{{ strtoupper($sub_judul) }}</b>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="20" height="5"></td>
                </tr>
            </table>

            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 8px; table-layout: fixed;">
                <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                    <th class="t l b" rowspan="2" width="2%">No</th>
                    <th class="t l b" rowspan="2" width="6%">LOAN ID</th>
                    <th class="t l b" rowspan="2" width="8%">NAMA KELOMPOK</th>
                    <th class="t l b" rowspan="2" width="6%">CIF / NO. ANGGOTA</th>
                    <th class="t l b" rowspan="2" width="4%">
                        <div>Tgl Cair</div>
                        <div><small>(dd/mm/yy)</small></div>
                    </th>
                    <th class="t l b" rowspan="2" width="3%">Bln</th>
                    <th class="t l b" rowspan="2" width="6%">Alokasi</th>
                    <th class="t l b" colspan="2">Target</th>
                    <th class="t l b" colspan="2">Real s.d. Bulan Lalu</th>
                    <th class="t l b" colspan="2">Real Bulan Ini</th>
                    <th class="t l b" colspan="2">Real s.d. Bulan Ini</th>
                    <th class="t l b" rowspan="2">Saldo</th>
                    <th class="t l b" rowspan="2" width="2%">%</th>
                    <th class="t l b r" colspan="2">Tunggakan</th>
                </tr>
                <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b" width="6%">Jasa</th>
                    <th class="t l b" width="6%">Pokok</th>
                    <th class="t l b r" width="6%">Jasa</th>
                </tr>

                @foreach ($pinkel_gabungan as $pinkel)
                    @php
                        $nomor++;
                    @endphp

                    @php
                        $saldo_pokok = $pinkel->alokasi;
                        $saldo_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100);
                        $real_pokok = 0;
                        $real_jasa = 0;
                        $sum_pokok = 0;
                        $sum_jasa = 0;
                        if ($pinkel->saldo) {
                            $real_pokok = $pinkel->saldo->realisasi_pokok;
                            $real_jasa = $pinkel->saldo->realisasi_jasa;
                            $sum_pokok = $pinkel->saldo->sum_pokok;
                            $sum_jasa = $pinkel->saldo->sum_jasa;
                            $saldo_pokok = $pinkel->saldo->saldo_pokok;
                            $saldo_jasa = $pinkel->saldo->saldo_jasa;
                        }
                        if ($saldo_jasa < 0) {
                            $saldo_jasa = 0;
                        }
                        if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                            $saldo_jasa = 0;
                        }

                        $target_pokok = 0;
                        $target_jasa = 0;
                        if ($pinkel->target) {
                            $target_pokok = $pinkel->target->target_pokok;
                            $target_jasa = $pinkel->target->target_jasa;
                        }

                        $tunggakan_pokok = $target_pokok - $sum_pokok;
                        if ($tunggakan_pokok < 0) {
                            $tunggakan_pokok = 0;
                        }
                        $tunggakan_jasa = $target_jasa - $sum_jasa;
                        if ($tunggakan_jasa < 0) {
                            $tunggakan_jasa = 0;
                        }

                        $pross = 1;
                        if ($target_pokok != 0) {
                            $pross = $sum_pokok / $target_pokok;
                        }

                        if ($pinkel->tgl_lunas <= $tgl_kondisi && in_array($pinkel->status, ['L', 'R', 'H'])) {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                        }

                        $pros_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->pros_jasa / $pinkel->jangka;
                    @endphp

                    <tr>
                        <td class="t l b" align="center">{{ $nomor }}</td>
                        <td class="t l b" align="left">{{ $pinkel->id }}</td>
                        <td class="t l b" align="left">{{ strtoupper($pinkel->nama_kelompok) }}</td>
                        <td class="t l b" align="left">{{ $pinkel->kd_kelompok }}</td>
                        <td class="t l b" align="center">{{ Tanggal::tglIndo($pinkel->tgl_cair, 'DD/MM/YY') }}</td>
                        <td class="t l b" align="center">
                            <small>{{ $pinkel->jangka }}*{{ number_format($pros_jasa, 2) }}</small>
                        </td>
                        <td class="t l b" align="right">{{ number_format($pinkel->alokasi) }}</td>
                        <td class="t l b" align="right">{{ number_format($target_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($target_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_pokok - $pinkel->real_sum_realisasi_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_jasa - $pinkel->real_sum_realisasi_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($pinkel->real_sum_realisasi_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($pinkel->real_sum_realisasi_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_pokok) }}</td>
                        <td class="t l b" align="right">{{ number_format($sum_jasa) }}</td>
                        <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                        <td class="t l b" align="center">{{ number_format(floor($pross * 100)) }}</td>

                        @if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L')
                            <td class="t l b r" colspan="2" align="center">V-LUNAS {{ Tanggal::tglIndo($pinkel->tgl_lunas) }}</td>
                        @elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'R')
                            <td class="t l b r" colspan="2" align="center">Rescedulling {{ Tanggal::tglIndo($pinkel->tgl_lunas) }}</td>
                        @elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'H')
                            <td class="t l b r" colspan="2" align="center">Penghapusan {{ Tanggal::tglIndo($pinkel->tgl_lunas) }}</td>
                        @else
                            <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                            <td class="t l b r" align="right">{{ number_format($tunggakan_jasa) }}</td>
                        @endif
                    </tr>

                    @php
                        $j_alokasi += $pinkel->alokasi;
                        $j_target_pokok += $target_pokok;
                        $j_target_jasa += $target_jasa;
                        $j_real_bl_pokok += $sum_pokok - $pinkel->real_sum_realisasi_pokok;
                        $j_real_bl_jasa += $sum_jasa - $pinkel->real_sum_realisasi_jasa;
                        $j_real_pokok += $pinkel->real_sum_realisasi_pokok;
                        $j_real_jasa += $pinkel->real_sum_realisasi_jasa;
                        $j_real_bi_pokok += $sum_pokok;
                        $j_real_bi_jasa += $sum_jasa;
                        $j_saldo_pokok += $saldo_pokok;
                        $j_tunggakan_pokok += $tunggakan_pokok;
                        $j_tunggakan_jasa += $tunggakan_jasa;
                    @endphp
                @endforeach

                @php
                    $t_alokasi += $j_alokasi;
                    $t_target_pokok += $j_target_pokok;
                    $t_target_jasa += $j_target_jasa;
                    $t_real_bl_pokok += $j_real_bl_pokok;
                    $t_real_bl_jasa += $j_real_bl_jasa;
                    $t_real_pokok += $j_real_pokok;
                    $t_real_jasa += $j_real_jasa;
                    $t_real_bi_pokok += $j_real_bi_pokok;
                    $t_real_bi_jasa += $j_real_bi_jasa;
                    $t_saldo_pokok += $j_saldo_pokok;
                    $t_tunggakan_pokok += $j_tunggakan_pokok;
                    $t_tunggakan_jasa += $j_tunggakan_jasa;

                    $j_pross = 1;
                    if ($j_target_pokok != 0) {
                        $j_pross = $j_real_bi_pokok / $j_target_pokok;
                    }
                @endphp
                @if (count($pinkel_gabungan) > 0)
                @php
                    $t_pross = 1;
                    if ($t_target_pokok != 0) {
                        $t_pross = $t_real_bi_pokok / $t_target_pokok;
                    }
                @endphp
                <tr style="font-weight: bold; background: rgb(230, 230, 230);">
                    <td class="t l b" align="center" colspan="7" height="15">TOTAL PINJAMAN KELOMPOK</td>
                    <td class="t l b" align="right">{{ number_format($t_alokasi) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_target_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_target_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bl_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bl_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bi_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_real_bi_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_saldo_pokok) }}</td>
                    <td class="t l b" align="center">{{ number_format(floor($t_pross * 100)) }}</td>
                    <td class="t l b" align="right">{{ number_format($t_tunggakan_pokok) }}</td>
                    <td class="t l b r" align="right">{{ number_format($t_tunggakan_jasa) }}</td>
                </tr>
                @endif
            </table>

            @php
                $ringkasan[] = [
                    'jenis' => 'Kelompok',
                    'alokasi' => $t_alokasi,
                    'target_pokok' => $t_target_pokok,
                    'target_jasa' => $t_target_jasa,
                    'real_bi_pokok' => $t_real_bi_pokok,
                    'real_bi_jasa' => $t_real_bi_jasa,
                    'saldo_pokok' => $t_saldo_pokok,
                    'tunggakan_pokok' => $t_tunggakan_pokok,
                    'tunggakan_jasa' => $t_tunggakan_jasa,
                ];
                $grand_alokasi += $t_alokasi;
                $grand_target_pokok += $t_target_pokok;
                $grand_target_jasa += $t_target_jasa;
                $grand_real_bi_pokok += $t_real_bi_pokok;
                $grand_real_bi_jasa += $t_real_bi_jasa;
                $grand_saldo_pokok += $t_saldo_pokok;
                $grand_tunggakan_pokok += $t_tunggakan_pokok;
                $grand_tunggakan_jasa += $t_tunggakan_jasa;
            @endphp
        @endif

        @if ($has_any)
            <div style="page-break-before: always;"></div>

            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr>
                    <td colspan="20" align="center">
                        <div style="font-size: 18px;">
                            <b>RINGKASAN PERKEMBANGAN PIUTANG</b>
                        </div>
                        <div style="font-size: 16px;">
                            <b>{{ strtoupper($sub_judul) }}</b>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="10"></td>
                </tr>
            </table>

            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px; table-layout: fixed;">
                <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                    <th class="t l b" rowspan="2" width="4%">No</th>
                    <th class="t l b" rowspan="2" width="15%">Jenis</th>
                    <th class="t l b" rowspan="2" width="13%">Alokasi</th>
                    <th class="t l b" colspan="2">Target</th>
                    <th class="t l b" colspan="2">Real s.d. Bulan Ini</th>
                    <th class="t l b" rowspan="2" width="13%">Saldo Pokok</th>
                    <th class="t l b" rowspan="2" width="6%">%</th>
                    <th class="t l b r" colspan="2">Tunggakan</th>
                </tr>
                <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                    <th class="t l b" width="9%">Pokok</th>
                    <th class="t l b" width="9%">Jasa</th>
                    <th class="t l b" width="9%">Pokok</th>
                    <th class="t l b" width="9%">Jasa</th>
                    <th class="t l b" width="8%">Pokok</th>
                    <th class="t l b r" width="8%">Jasa</th>
                </tr>

                @foreach ($ringkasan as $i => $r)
                    @php
                        $row_pross = 1;
                        if ($r['target_pokok'] != 0) {
                            $row_pross = $r['real_bi_pokok'] / $r['target_pokok'];
                        }
                    @endphp
                    <tr>
                        <td class="t l b" align="center">{{ $i + 1 }}</td>
                        <td class="t l b" align="left">{{ $r['jenis'] }}</td>
                        <td class="t l b" align="right">{{ number_format($r['alokasi']) }}</td>
                        <td class="t l b" align="right">{{ number_format($r['target_pokok']) }}</td>
                        <td class="t l b" align="right">{{ number_format($r['target_jasa']) }}</td>
                        <td class="t l b" align="right">{{ number_format($r['real_bi_pokok']) }}</td>
                        <td class="t l b" align="right">{{ number_format($r['real_bi_jasa']) }}</td>
                        <td class="t l b" align="right">{{ number_format($r['saldo_pokok']) }}</td>
                        <td class="t l b" align="center">{{ number_format(floor($row_pross * 100)) }}</td>
                        <td class="t l b" align="right">{{ number_format($r['tunggakan_pokok']) }}</td>
                        <td class="t l b r" align="right">{{ number_format($r['tunggakan_jasa']) }}</td>
                    </tr>
                @endforeach

                @php
                    $grand_pross = 1;
                    if ($grand_target_pokok != 0) {
                        $grand_pross = $grand_real_bi_pokok / $grand_target_pokok;
                    }
                @endphp
                <tr style="font-weight: bold; background: rgb(230, 230, 230);">
                    <td class="t l b" align="center" colspan="2">JUMLAH</td>
                    <td class="t l b" align="right">{{ number_format($grand_alokasi) }}</td>
                    <td class="t l b" align="right">{{ number_format($grand_target_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($grand_target_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($grand_real_bi_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($grand_real_bi_jasa) }}</td>
                    <td class="t l b" align="right">{{ number_format($grand_saldo_pokok) }}</td>
                    <td class="t l b" align="center">{{ number_format(floor($grand_pross * 100)) }}</td>
                    <td class="t l b" align="right">{{ number_format($grand_tunggakan_pokok) }}</td>
                    <td class="t l b r" align="right">{{ number_format($grand_tunggakan_jasa) }}</td>
                </tr>
            </table>

            @php
                $signature = is_string($kec->ttd->tanda_tangan_pelaporan ?? null)
                    ? str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan)
                    : '';
                $signature_decoded = $signature !== '' ? json_decode($signature, true) : null;
                $signature_html = is_string($signature_decoded) ? $signature_decoded : (is_array($signature_decoded) ? json_encode($signature_decoded) : $signature);
            @endphp
            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; margin-top: 20px;">
                <tr>
                    <td colspan="14">
                        <div style="margin-top: 16px;"></div>
                        {!! $signature_html !!}
                    </td>
                </tr>
            </table>
        @endif
    @endif
@endsection
