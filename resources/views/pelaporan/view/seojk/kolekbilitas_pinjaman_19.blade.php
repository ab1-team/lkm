@php
    use App\Utils\Tanggal;
    $section = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    @php
        $kolek_items = [
            ['nama' => 'Lancar', 'prosentase' => '0', 'durasi' => '6', 'satuan' => 'bulan'],
            ['nama' => 'Diragukan', 'prosentase' => '50', 'durasi' => '12', 'satuan' => 'bulan'],
            ['nama' => 'Macet', 'prosentase' => '100', 'durasi' => '9999', 'satuan' => 'bulan'],
        ];

        $jumlah_kolek = count($kolek_items);
        $nomor = 0;
        $kd_desa_all = [];

        $t_alokasi = 0;
        $t_saldo = 0;
        $t_tunggakan_pokok = 0;
        $t_tunggakan_jasa = 0;
        $t_kolek_total = [];
        for ($i = 1; $i <= $jumlah_kolek; $i++) {
            $t_kolek_total[$i] = 0;
        }

        $j_alokasi = 0;
        $j_saldo = 0;
        $j_tunggakan_pokok = 0;
        $j_tunggakan_jasa = 0;
        for ($i = 1; $i <= $jumlah_kolek; $i++) {
            ${"j_kolek{$i}"} = 0;
        }
    @endphp

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="6" align="center">
                <div style="font-size: 18px;">
                    <b>DAFTAR KOLEKTIBILITAS PINJAMAN</b>
                </div>
                <div style="font-size: 14px;">
                    <b>(Berdasarkan POJK No. 19/POJK.05/2021)</b>
                </div>
                <div style="font-size: 14px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="6" height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
        <tr>
            <th class="t l b" width="2%">No</th>
            <th class="t l b" width="20%">Kreditur - Loan ID</th>
            <th class="t l b" width="15%">Jenis</th>
            <th class="t l b" width="8%">Saldo</th>
            <th class="t l b" width="8%">Tunggakan</th>
            <th class="t l b" width="3%">NH</th>
            @foreach ($kolek_items as $idx => $kolek_item)
                @php $kolek_num = $idx + 1; @endphp
                <th class="t l b r" width="{{ $idx == $jumlah_kolek - 1 ? 14 : 10 }}%">{{ $kolek_item['nama'] }}</th>
            @endforeach
        </tr>

        @foreach ($jenis_pp as $jpp)
            @foreach ($jpp->pinjaman_individu as $pinkel)
                @php
                    $kd_desa_all[] = $pinkel->kd_desa;
                    $desa = $pinkel->kd_desa;
                @endphp

                @if (array_count_values($kd_desa_all)[$pinkel->kd_desa] <= '1')
                    @if ($section != $desa && count($kd_desa_all) > 1)
                        @php
                            $t_alokasi += $j_alokasi;
                            $t_saldo += $j_saldo;
                            $t_tunggakan_pokok += $j_tunggakan_pokok;
                            $t_tunggakan_jasa += $j_tunggakan_jasa;

                            for ($i = 1; $i <= $jumlah_kolek; $i++) {
                                $t_kolek_total[$i] += ${"j_kolek{$i}"};
                            }
                        @endphp
                        <tr style="font-weight: bold;">
                            <td class="t l b" align="left" colspan="3">Jumlah {{ $nama_desa }}</td>
                            <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
                            <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
                            <td class="t l b" align="right">&nbsp;</td>
                            @for ($i = 1; $i <= $jumlah_kolek; $i++)
                                @php $kolek_val = ${"j_kolek{$i}"} ?? 0; @endphp
                                <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($kolek_val) }}</td>
                            @endfor
                        </tr>
                    @endif

                    <tr style="font-weight: bold;">
                        <td class="t l b r" colspan="{{ 6 + $jumlah_kolek }}" align="left">{{ $pinkel->kode_desa }}. {{ $pinkel->nama_desa }}</td>
                    </tr>

                    @php
                        $nomor = 1;
                        $j_alokasi = 0;
                        $j_saldo = 0;
                        $j_tunggakan_pokok = 0;
                        $j_tunggakan_jasa = 0;

                        for ($i = 1; $i <= $jumlah_kolek; $i++) {
                            ${"j_kolek{$i}"} = 0;
                        }

                        $section = $pinkel->kd_desa;
                        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
                    @endphp
                @endif

                @php
                    $sum_pokok = 0;
                    $sum_jasa = 0;
                    $saldo_pokok = $pinkel->alokasi;
                    $saldo_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100);
                    if ($pinkel->saldo) {
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
                    $wajib_pokok = 0;
                    $wajib_jasa = 0;
                    $angsuran_ke = 0;
                    $jatuh_tempo = 0;
                    if ($pinkel->target) {
                        $target_pokok = $pinkel->target->target_pokok;
                        $target_jasa = $pinkel->target->target_jasa;
                        $wajib_pokok = $pinkel->target->wajib_pokok;
                        $wajib_jasa = $pinkel->target->wajib_jasa;
                        $angsuran_ke = $pinkel->target->angsuran_ke;
                        $jatuh_tempo = $pinkel->target->jatuh_tempo;
                    }

                    $tunggakan_pokok = $target_pokok - $sum_pokok;
                    if ($tunggakan_pokok < 0) {
                        $tunggakan_pokok = 0;
                    }
                    $tunggakan_jasa = $target_jasa - $sum_jasa;
                    if ($tunggakan_jasa < 0) {
                        $tunggakan_jasa = 0;
                    }

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && ($pinkel->status == 'L' || $pinkel->status == 'R' || $pinkel->status == 'H')) {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                    }

                    $tgl_cair = explode('-', $pinkel->tgl_cair);
                    $th_cair = $tgl_cair[0];
                    $bl_cair = $tgl_cair[1];
                    $tg_cair = $tgl_cair[2];

                    $tgl_akhir = new DateTime($tgl_kondisi);
                    $tgl_awal = new DateTime($pinkel->tgl_cair);
                    $selisih = $tgl_akhir->diff($tgl_awal);
                    $selisih = $selisih->y * 12 + $selisih->m;

                    $jum_nunggak = ceil($wajib_pokok == 0 ? 0 : $tunggakan_pokok/$wajib_pokok);

                    $_kolek = 0;
                    if ($wajib_pokok != '0') {
                        $_kolek = $tunggakan_pokok / $wajib_pokok;
                    }
                    $kolek_bulan = round($_kolek + ($selisih - $angsuran_ke));

                    $kolek_hari = 0;
                    if ($tunggakan_pokok <= 0) {
                        $kolek_hari = 0;
                    } elseif ($jatuh_tempo != 0) {
                        $kolek_hari = round((strtotime($tgl_kondisi) - strtotime($jatuh_tempo)) / (60 * 60 * 24))+(($jum_nunggak-1)*30);
                        if ($kolek_hari < 0) {
                            $kolek_hari = 0;
                        }
                    }

                    for ($i = 1; $i <= $jumlah_kolek; $i++) {
                        ${"kolek{$i}"} = 0;
                    }

                    $matched = false;
                    foreach ($kolek_items as $idx => $item) {
                        $kolekNum = $idx + 1;
                        if (!is_array($item) || !isset($item['durasi'], $item['satuan'])) continue;

                        $durasi = (int) $item['durasi'];
                        $match = false;
                        if ($item['satuan'] === 'hari' && isset($kolek_hari) && $kolek_hari < $durasi) {
                            $match = true;
                            $kolek = $kolek_hari;
                        } elseif ($item['satuan'] === 'bulan' && isset($kolek_bulan) && $kolek_bulan < $durasi) {
                            $match = true;
                            $kolek = $kolek_bulan;
                        }

                        if ($match) {
                            ${"kolek{$kolekNum}"} = $saldo_pokok;
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched && $jumlah_kolek > 0) {
                        ${"kolek{$jumlah_kolek}"} = $saldo_pokok;
                    }
                @endphp

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b" align="left">{{ $pinkel->namadepan }} - {{ $pinkel->id }}</td>
                    <td class="t l b" align="left">{{ $jpp->nama_jpp }}</td>
                    <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                    <td class="t l b" align="right">{{ $kolek_bulan ?? 0 }}</td>
                    @for ($i = 1; $i <= $jumlah_kolek; $i++)
                        @php $kolek_val = ${"kolek{$i}"} ?? 0; @endphp
                        <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($kolek_val) }}</td>
                    @endfor
                </tr>

                @php
                    $j_alokasi += $pinkel->alokasi;
                    $j_saldo += $saldo_pokok;
                    $j_tunggakan_pokok += $tunggakan_pokok;
                    $j_tunggakan_jasa += $tunggakan_jasa;
                    for ($i = 1; $i <= $jumlah_kolek; $i++) {
                        ${"j_kolek{$i}"} += ${"kolek{$i}"};
                    }
                @endphp
            @endforeach
        @endforeach

        @if (count($kd_desa_all) > 0)
            @php
                $t_alokasi += $j_alokasi;
                $t_saldo += $j_saldo;
                $t_tunggakan_pokok += $j_tunggakan_pokok;
                $t_tunggakan_jasa += $j_tunggakan_jasa;
                for ($i = 1; $i <= $jumlah_kolek; $i++) {
                    $t_kolek_total[$i] += ${"j_kolek{$i}"};
                }
            @endphp

            <tr style="font-weight: bold;">
                <td class="t l b" align="left" colspan="3">Jumlah {{ $nama_desa }}</td>
                <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
                <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
                <td class="t l b" align="right">&nbsp;</td>
                @for ($i = 1; $i <= $jumlah_kolek; $i++)
                    @php $j_kolek_val = ${"j_kolek{$i}"} ?? 0; @endphp
                    <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($j_kolek_val) }}</td>
                @endfor
            </tr>

            <tr>
                <td colspan="{{ 6 + $jumlah_kolek }}" style="padding: 0px !important;">
                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
                        <tr style="font-weight: bold;">
                            <td class="t l b" align="center" colspan="3" height="20">J U M L A H</td>
                            <td class="t l b" align="right">{{ number_format($t_saldo) }}</td>
                            <td class="t l b r" align="right">{{ number_format($t_tunggakan_pokok) }}</td>
                            <td class="t l b" align="right">&nbsp;</td>
                            @for ($i = 1; $i <= $jumlah_kolek; $i++)
                                <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($t_kolek_total[$i]) }}</td>
                            @endfor
                        </tr>

                        <tr style="font-weight: bold;">
                            <td class="t l b" align="center" colspan="3" rowspan="2" height="20">Penyisihan Penghapusan Piutang (PPAP)</td>
                            <td class="t l b" colspan="2" align="center">Jumlah PPAP</td>
                            <td class="t l b" align="right">&nbsp;</td>
                            @foreach ($kolek_items as $idx => $item)
                                @php $kolek_num = $idx + 1; @endphp
                                <td class="t l b {{ $kolek_num == $jumlah_kolek ? 'r' : '' }}" align="center">{{ $item['nama'] }} * {{ $item['prosentase'] }}%</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="t l b" align="center" colspan="3">
                                @php
                                    $total_ppap = 0;
                                    foreach ($kolek_items as $idx => $item) {
                                        $kolek_num = $idx + 1;
                                        $nilai_kolek = $t_kolek_total[$kolek_num] ?? 0;
                                        $prosentase = (float) $item['prosentase'];
                                        $total_ppap += ($nilai_kolek * $prosentase / 100);
                                    }
                                @endphp
                                {{ number_format($total_ppap) }}
                            </td>
                            <td class="t l b" align="right">&nbsp;</td>
                            @foreach ($kolek_items as $idx => $item)
                                @php
                                    $kolek_num = $idx + 1;
                                    $nilai_kolek = $t_kolek_total[$kolek_num] ?? 0;
                                    $prosentase = (float) $item['prosentase'];
                                    $ppap = ($nilai_kolek * $prosentase) / 100;
                                @endphp
                                <td class="t l b {{ $kolek_num == $jumlah_kolek ? 'r' : '' }}" align="center">{{ number_format($ppap) }}</td>
                            @endforeach
                        </tr>

                        <tr>
                            <td colspan="{{ 6 + $jumlah_kolek }}" style="padding: 0px !important;">
                                <p style="font-size: 9px;">
                                    Keterangan (POJK No. 19/POJK.05/2021):<br>
                                    @foreach ($kolek_items as $idx => $item)
                                        @php
                                            $is_last = ($idx == count($kolek_items) - 1);
                                            $prev_durasi = $idx > 0 ? $kolek_items[$idx - 1]['durasi'] : 0;
                                        @endphp
                                        - <b>{{ $item['nama'] }}</b>: tunggakan {{ $idx == 0 ? 'kurang dari ' . $item['durasi'] . ' ' . $item['satuan'] : ($is_last ? 'lebih dari ' . $prev_durasi . ' ' . $item['satuan'] : 'antara ' . $prev_durasi . ' s.d. ' . $item['durasi'] . ' ' . $item['satuan']) }}, PPAP {{ $item['prosentase'] }}%<br>
                                    @endforeach
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="{{ 6 + $jumlah_kolek }}">
                                <div style="margin-top: 16px;"></div>
                                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif
    </table>
@endsection
