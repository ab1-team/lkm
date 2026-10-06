@php
    use App\Utils\Tanggal;
    $nomor_jenis_pp = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    @foreach ($jenis_pp as $jpp)
        @php
            if ($jpp->pinjaman_anggota->isEmpty()) {
                continue;
            }
        @endphp

        @php
            $nomor = 0;
            $t_alokasi = 0;
            $t_saldo = 0;
            $t_tunggakan_pokok = 0;
            $t_tunggakan_jasa = 0;

            // Parse JSON kolek configuration
            $klk = json_decode($kec->kolek, true);
            
            // Filter hanya item yang tidak null
            $kolek_items = [];
            if (is_array($klk)) {
                foreach ($klk as $index => $item) {
                    // Hanya include jika nama tidak null
                    if (!empty($item['nama'])) {
                        $kolek_items[] = $item;
                    }
                }
            }
            
            // Jumlah kolom kolektibilitas yang aktif
            $jumlah_kolek = count($kolek_items);
            
            // Inisialisasi total untuk setiap kolom kolek
            $t_kolek_total = [];
            for ($i = 1; $i <= $jumlah_kolek; $i++) {
                $t_kolek_total[$i] = 0;
            }
        @endphp

        @if ($nomor_jenis_pp != 0)
            <div class="break"></div>
        @endif

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>Cadangan Kerugian Piutang {{ $jpp->nama_jpp }}</b>
                    </div>
                    <div style="font-size: 16px;">
                        <b>{{ strtoupper($sub_judul) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="5"></td>
            </tr>
        </table>

        @php
            $j_alokasi = 0;
            $j_saldo = 0;
            $j_tunggakan_pokok = 0;
            $j_tunggakan_jasa = 0;
            for ($i = 1; $i <= $jumlah_kolek; $i++) {
                ${"j_kolek{$i}"} = 0;
            }
        @endphp

        @foreach ($jpp->pinjaman_anggota as $pinkel)
            @php
                $nomor++;
            @endphp

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

                $pross = $saldo_pokok == 0 ? 0 : $saldo_pokok / $pinkel->alokasi;

                if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'R') {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'H') {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                }

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

                // Inisialisasi semua kolom kolek berdasarkan jumlah aktif
                for ($i = 1; $i <= $jumlah_kolek; $i++) {
                    ${"kolek{$i}"} = 0;
                }

                // Logika penentuan kolektibilitas
                $matched = false;
                foreach ($kolek_items as $idx => $item) {
                    $kolekNum = $idx + 1;
                    
                    if (!is_array($item) || !isset($item['durasi'], $item['satuan'])) {
                        continue;
                    }

                    $durasi = (int) $item['durasi'];
                    $match = false;
                    
                    if ($item['satuan'] === 'hari' && isset($kolek_hari) && $kolek_hari < $durasi) {
                        $match = true;
                    } elseif ($item['satuan'] === 'bulan' && isset($kolek_bulan) && $kolek_bulan < $durasi) {
                        $match = true;
                    }

                    if ($match) {
                        ${"kolek{$kolekNum}"} = $saldo_pokok;
                        $matched = true;
                        break;
                    }
                }

                // Jika tidak ada yang cocok, masukkan ke kategori terakhir
                if (!$matched && $jumlah_kolek > 0) {
                    ${"kolek{$jumlah_kolek}"} = $saldo_pokok;
                }

                $j_alokasi += $pinkel->alokasi;
                $j_saldo += $saldo_pokok;
                $j_tunggakan_pokok += $tunggakan_pokok;
                $j_tunggakan_jasa += $tunggakan_jasa;
                
                for ($i = 1; $i <= $jumlah_kolek; $i++) {
                    ${"j_kolek{$i}"} += ${"kolek{$i}"};
                }
            @endphp
        @endforeach

        @if ($nomor > 0)
            @php
                $j_pross = $j_saldo / $j_alokasi;
                $t_alokasi += $j_alokasi;
                $t_saldo += $j_saldo;
                $t_tunggakan_pokok += $j_tunggakan_pokok;
                $t_tunggakan_jasa += $j_tunggakan_jasa;
                
                for ($i = 1; $i <= $jumlah_kolek; $i++) {
                    $t_kolek_total[$i] += ${"j_kolek{$i}"};
                }

                $t_pros = 0;
                if ($t_saldo) {
                    $t_pross = $t_saldo / $t_alokasi;
                }
            @endphp

            <table border="1" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr>
                    <th height="20" width="10">No</th>
                    <th width="200">Tingkat Kolektibilitas</th>
                    <th width="30">%</th>
                    <th width="150">Saldo Piutang</th>
                    <th>Beban Penyisihan Penghapusan Piutang</th>
                    <th width="150">NPL</th>
                </tr>
                <tr>
                    <td align="center">a</td>
                    <td align="center">b</td>
                    <td align="center">c</td>
                    <td align="center">d</td>
                    <td align="center">e = c * d</td>
                    <td align="center" >f = (selain lancar) / total saldo</td>
                </tr>

                @php
                    $no_urut = 1;
                    $total_saldo = 0;
                    $total_beban = 0;
                    
                    // Hitung total saldo dan beban
                    foreach ($kolek_items as $idx => $item) {
                        $kolekNum = $idx + 1;
                        $nilai_kolek = $t_kolek_total[$kolekNum] ?? 0;
                        $prosentase = (float) $item['prosentase'];
                        $beban = ($nilai_kolek * $prosentase) / 100;
                        $total_saldo += $nilai_kolek;
                        $total_beban += $beban;
                    }
                @endphp

                @foreach ($kolek_items as $idx => $item)
                    @php
                        $kolekNum = $idx + 1;
                        $nilai_kolek = $t_kolek_total[$kolekNum] ?? 0;
                        $prosentase = (float) $item['prosentase'];
                        $beban = ($nilai_kolek * $prosentase) / 100;
                        $npl = $total_saldo > 0 ? (($total_saldo - $t_kolek_total[1]) / $total_saldo) * 100 : 0;
                    @endphp
                    <tr>
                        <td align="center">{{ $no_urut }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td align="center">{{ $prosentase }}%</td>
                        <td align="right">{{ number_format($nilai_kolek) }}</td>
                        <td align="right">{{ number_format($beban) }}</td>
                        @if ($idx == 0)
                            <td align="center" rowspan="{{ $jumlah_kolek +1}}">
                                {{ round($npl, 2) }}%
                            </td>
                        @endif
                    </tr>
                    @php $no_urut++; @endphp
                @endforeach

                <tr style="font-weight: bold;">
                    <th colspan="3" height="15">Total</th>
                    <th align="right">{{ number_format($total_saldo) }}</th>
                    <th align="right">{{ number_format($total_beban) }}</th>
                </tr>
            </table>

            <div style="margin-top: 16px;"></div>
            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
        @endif

        @php
            $nomor_jenis_pp++;
        @endphp
    @endforeach
@endsection
