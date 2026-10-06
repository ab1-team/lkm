@php
    use App\Utils\Tanggal;

    // Flatten seluruh produk pinjaman menjadi satu daftar (tanpa pengelompokan desa)
    $pinjaman_gabungan = collect();
    foreach ($jenis_pp_i as $jpp_i) {
        foreach ($jpp_i->pinjaman_individu as $pinkel) {
            $pinkel->nama_jpp = $jpp_i->nama_jpp;
            $pinjaman_gabungan->push($pinkel);
        }
    }

    // Urutkan berdasarkan Tanggal Pencairan ascending
    $pinjaman_gabungan = $pinjaman_gabungan->sortBy([
        ['tgl_cair', 'asc'],
        ['id', 'asc'],
    ])->values();
@endphp

@extends('pelaporan.layout.base')

@section('content')
    @php
        $kolek_items = [
            ['nama' => 'Lancar', 'prosentase' => '0', 'durasi' => '10', 'satuan' => 'hari'],
            ['nama' => 'Dalam Perhatian Khusus', 'prosentase' => '5', 'durasi' => '90', 'satuan' => 'hari'],
            ['nama' => 'Kurang Lancar', 'prosentase' => '15', 'durasi' => '120', 'satuan' => 'hari'],
            ['nama' => 'Diragukan', 'prosentase' => '50', 'durasi' => '180', 'satuan' => 'hari'],
            ['nama' => 'Macet', 'prosentase' => '100', 'durasi' => '9999', 'satuan' => 'hari'],
        ];

        $jumlah_kolek = count($kolek_items);
        $nomor = 0;

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

        // No + Loan ID + Nama + CIF + NIK + Jenis + Saldo + Tunggakan + DPD + kolek
        $jumlah_kolom = 9 + $jumlah_kolek;
    @endphp

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="{{ $jumlah_kolom }}" align="center">
                <div style="font-size: 18px;">
                    <b>DAFTAR KOLEKTIBILITAS PINJAMAN</b>
                </div>
                <div style="font-size: 14px;">
                    <b>(Berdasarkan POJK No. 41 Tahun 2024)</b>
                </div>
                <div style="font-size: 14px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="{{ $jumlah_kolom }}" height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px; table-layout: fixed;">
        <tr>
            <th class="t l b" width="3%">No</th>
            <th class="t l b" width="9%">LOAN ID</th>
            <th class="t l b" width="12%">NAMA DEBITUR</th>
            <th class="t l b" width="8%">CIF / NO. ANGGOTA</th>
            <th class="t l b" width="8%">NIK</th>
            <th class="t l b" width="9%">Jenis</th>
            <th class="t l b" width="6%">Saldo</th>
            <th class="t l b" width="6%">Tunggakan</th>
            <th class="t l b" width="4%">DPD</th>
            @foreach ($kolek_items as $idx => $kolek_item)
                @php $kolek_num = $idx + 1; @endphp
                <th class="t l b r" width="{{ $idx == $jumlah_kolek - 1 ? 7.6 : 6.8 }}%">{{ $kolek_item['nama'] }}</th>
            @endforeach
        </tr>

        @foreach ($pinjaman_gabungan as $pinkel)
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
                    if ($saldo_jasa < 0) $saldo_jasa = 0;
                    if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') $saldo_jasa = 0;

                    $target_pokok = 0; $target_jasa = 0; $wajib_pokok = 0; $wajib_jasa = 0;
                    $angsuran_ke = 0; $jatuh_tempo = 0;
                    if ($pinkel->target) {
                        $target_pokok = $pinkel->target->target_pokok;
                        $target_jasa = $pinkel->target->target_jasa;
                        $wajib_pokok = $pinkel->target->wajib_pokok;
                        $wajib_jasa = $pinkel->target->wajib_jasa;
                        $angsuran_ke = $pinkel->target->angsuran_ke;
                        $jatuh_tempo = $pinkel->target->jatuh_tempo;
                    }

                    $tunggakan_pokok = $target_pokok - $sum_pokok;
                    if ($tunggakan_pokok < 0) $tunggakan_pokok = 0;
                    $tunggakan_jasa = $target_jasa - $sum_jasa;
                    if ($tunggakan_jasa < 0) $tunggakan_jasa = 0;

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && in_array($pinkel->status, ['L','R','H'])) {
                        $tunggakan_pokok = 0; $tunggakan_jasa = 0; $saldo_pokok = 0; $saldo_jasa = 0;
                    }

                    $tgl_cair = explode('-', $pinkel->tgl_cair);
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
                        $kolek_hari = round((strtotime($tgl_kondisi) - strtotime($jatuh_tempo)) / (60 * 60 * 24)) + (($jum_nunggak-1) * 30);
                        if ($kolek_hari < 0) $kolek_hari = 0;
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
                    <td class="t l b" align="left">{{ $pinkel->id }}</td>
                    <td class="t l b" align="left">{{ strtoupper($pinkel->namadepan) }}</td>
                    <td class="t l b" align="left">{{ $pinkel->nia }}</td>
                    <td class="t l b" align="left">{{ $pinkel->nik }}</td>
                    <td class="t l b" align="left">{{ strtoupper($pinkel->nama_jpp) }}</td>
                    <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                    <td class="t l b" align="right">{{ $kolek_hari ?? 0 }}</td>
                    @for ($i = 1; $i <= $jumlah_kolek; $i++)
                        @php $kolek_val = ${"kolek{$i}"} ?? 0; @endphp
                        <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($kolek_val) }}</td>
                    @endfor
                </tr>

                @php
                    $t_alokasi += $pinkel->alokasi;
                    $t_saldo += $saldo_pokok;
                    $t_tunggakan_pokok += $tunggakan_pokok;
                    $t_tunggakan_jasa += $tunggakan_jasa;
                    for ($i = 1; $i <= $jumlah_kolek; $i++) {
                        $t_kolek_total[$i] += ${"kolek{$i}"};
                    }
                @endphp
            @endforeach

        @if ($pinjaman_gabungan->count() > 0)
            <tr>
                <td colspan="{{ $jumlah_kolom }}" style="padding: 0px !important;">
                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px; table-layout: fixed;">
                        <tr style="font-weight: bold;">
                            <td class="t l b" align="center" colspan="6" height="20">J U M L A H</td>
                            <td class="t l b" align="right">{{ number_format($t_saldo) }}</td>
                            <td class="t l b r" align="right">{{ number_format($t_tunggakan_pokok) }}</td>
                            <td class="t l b" align="right">&nbsp;</td>
                            @for ($i = 1; $i <= $jumlah_kolek; $i++)
                                <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($t_kolek_total[$i]) }}</td>
                            @endfor
                        </tr>

                        <tr style="font-weight: bold;">
                            <td class="t l b" align="center" colspan="6" rowspan="2" height="20">Penyisihan Penghapusan Piutang (PPAP)</td>
                            <td class="t l b" colspan="2" align="center">Jumlah PPAP</td>
                            <td class="t l b" align="right">&nbsp;</td>
                            @foreach ($kolek_items as $idx => $item)
                                @php $kolek_num = $idx + 1; @endphp
                                <td class="t l b {{ $kolek_num == $jumlah_kolek ? 'r' : '' }}" align="center">{{ $item['nama'] }} * {{ $item['prosentase'] }}%</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="t l b" align="center" colspan="2">
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
                            <td colspan="{{ $jumlah_kolom }}" style="padding: 0px !important;">
                                <p style="font-size: 9px;">
                                    Keterangan (POJK No. 41 Tahun 2024):<br>
                                    @foreach ($kolek_items as $idx => $item)
                                        @php
                                            $is_last = ($idx == count($kolek_items) - 1);
                                            $prev_durasi = $idx > 0 ? $kolek_items[$idx - 1]['durasi'] : 0;
                                        @endphp
                                        - <b>{{ $item['nama'] }}</b>: keterlambatan {{ $idx == 0 ? 's.d. ' . $item['durasi'] . ' ' . $item['satuan'] : ($is_last ? 'lebih dari ' . $prev_durasi . ' ' . $item['satuan'] : '> ' . $prev_durasi . ' s.d. ' . $item['durasi'] . ' ' . $item['satuan']) }}, PPAP {{ $item['prosentase'] }}%<br>
                                    @endforeach
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="{{ $jumlah_kolom }}">
                                <div style="margin-top: 16px;"></div>
                                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif
    </table>

    <div style="page-break-before: always;"></div>

    @php
        $col_count = 3 + $jumlah_kolek + 2;
    @endphp
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="{{ $col_count }}" align="center">
                <div style="font-size: 18px;">
                    <b>RINGKASAN KOLEKTIBILITAS PINJAMAN</b>
                </div>
                <div style="font-size: 14px;">
                    <b>(Berdasarkan POJK No. 41 Tahun 2024)</b>
                </div>
                <div style="font-size: 16px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="{{ $col_count }}" height="10"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px; table-layout: fixed;">
        <tr style="background: rgb(230, 230, 230); font-weight: bold;">
            <th class="t l b" rowspan="2" width="4%">No</th>
            <th class="t l b" rowspan="2" width="15%">Jenis</th>
            <th class="t l b" rowspan="2" width="11%">Saldo Pokok</th>
            <th class="t l b" rowspan="2" width="11%">Tunggakan Pokok</th>
            @foreach ($kolek_items as $idx => $kolek_item)
                @php $kolek_num = $idx + 1; @endphp
                <th class="t l b {{ $kolek_num == $jumlah_kolek ? 'r' : '' }}" width="{{ 42 / $jumlah_kolek }}%">{{ $kolek_item['nama'] }}</th>
            @endforeach
            <th class="t l b r" rowspan="2" width="11%">Total Resiko</th>
        </tr>
        <tr style="background: rgb(230, 230, 230); font-weight: bold;">
            @foreach ($kolek_items as $idx => $kolek_item)
                @php $kolek_num = $idx + 1; @endphp
                <th class="t l b {{ $kolek_num == $jumlah_kolek ? 'r' : '' }}">{{ $kolek_item['prosentase'] }}%</th>
            @endforeach
        </tr>

        @php
            $row_total_risiko_41 = 0;
            foreach ($kolek_items as $idx => $item) {
                $kolek_num = $idx + 1;
                $nilai_kolek = $t_kolek_total[$kolek_num] ?? 0;
                $prosentase = (float) $item['prosentase'];
                $row_total_risiko_41 += ($nilai_kolek * $prosentase / 100);
            }
        @endphp
        <tr>
            <td class="t l b" align="center">1</td>
            <td class="t l b" align="left">Semua Jenis</td>
            <td class="t l b" align="right">{{ number_format($t_saldo) }}</td>
            <td class="t l b" align="right">{{ number_format($t_tunggakan_pokok) }}</td>
            @for ($i = 1; $i <= $jumlah_kolek; $i++)
                @php $kolek_val = $t_kolek_total[$i] ?? 0; @endphp
                <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($kolek_val) }}</td>
            @endfor
            <td class="t l b r" align="right">{{ number_format($row_total_risiko_41) }}</td>
        </tr>

        <tr style="font-weight: bold; background: rgb(230, 230, 230);">
            <td class="t l b" align="center" colspan="2">JUMLAH</td>
            <td class="t l b" align="right">{{ number_format($t_saldo) }}</td>
            <td class="t l b" align="right">{{ number_format($t_tunggakan_pokok) }}</td>
            @for ($i = 1; $i <= $jumlah_kolek; $i++)
                @php $kolek_val = $t_kolek_total[$i] ?? 0; @endphp
                <td class="t l b {{ $i == $jumlah_kolek ? 'r' : '' }}" align="right">{{ number_format($kolek_val) }}</td>
            @endfor
            <td class="t l b r" align="right">{{ number_format($row_total_risiko_41) }}</td>
        </tr>
    </table>
@endsection
