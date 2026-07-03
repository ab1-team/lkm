@php
    use App\Utils\Tanggal;
    $section = 0;
    $nomor_jenis_pp = 0;

    // Ambil data kolek dari database
    $kolekData = $kec->kolek ? json_decode($kec->kolek, true) : [];

    // Filter hanya kolek yang aktif (ada nama)
    $activeKolek = array_filter($kolekData, function($k) {
        return !empty($k['nama']);
    });

    // Fungsi untuk menentukan tingkat kolek (sama dengan kolek_desa)
    function getTingkatKolekCadangan($kolek_bulan, $kolekData) {
        if (empty($kolekData)) {
            return 0;
        }

        for ($i = 0; $i < count($kolekData); $i++) {
            $kolek = $kolekData[$i];

            if (empty($kolek['nama'])) {
                continue;
            }

            $durasi = floatval($kolek['durasi']);
            $satuan = $kolek['satuan'];

            if ($satuan == 'hari') {
                $durasi = $durasi / 30;
            }

            if ($kolek_bulan < $durasi) {
                return $i;
            }
        }

        for ($i = count($kolekData) - 1; $i >= 0; $i--) {
            if (!empty($kolekData[$i]['nama'])) {
                return $i;
            }
        }

        return 0;
    }
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
            $kd_desa = [];
            $nomor = 1;
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
                    if (!empty($item['nama'])) {
                        $kolek_items[] = $item;
                    }
                }
            }
            
            $jumlah_kolek = count($kolek_items);
            
            // Inisialisasi total untuk setiap kolom kolek (0-based index)
            $t_kolek = array_fill(0, count($kolekData), 0);
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

        @foreach ($jpp->pinjaman_anggota as $pinkel)
            @php
                $kd_desa[] = $pinkel->kd_desa;
                $desa = $pinkel->kd_desa;
            @endphp

            @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1')
                @if ($section != $desa && count($kd_desa) > 1)
                    @php
                        $j_pross = $j_saldo / $j_alokasi;
                        $t_alokasi += $j_alokasi;
                        $t_saldo += $j_saldo;
                        $t_tunggakan_pokok += $j_tunggakan_pokok;
                        $t_tunggakan_jasa += $j_tunggakan_jasa;
                        
                        foreach ($j_kolek as $idx => $val) {
                            $t_kolek[$idx] += $val;
                        }
                    @endphp
                @endif

                @php
                    $j_alokasi = 0;
                    $j_saldo = 0;
                    $j_tunggakan_pokok = 0;
                    $j_tunggakan_jasa = 0;
                    $j_kolek = array_fill(0, count($kolekData), 0);
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

                $target_pokok = 0;
                $target_jasa = 0;
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                if ($pinkel->target) {
                    $target_pokok = $pinkel->target->target_pokok;
                    $target_jasa = $pinkel->target->target_jasa;
                    $wajib_pokok = $pinkel->target->wajib_pokok;
                    $wajib_jasa = $pinkel->target->wajib_jasa;
                    $angsuran_ke = $pinkel->target->angsuran_ke;
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

                if ($pinkel->tgl_lunas <= $tgl_kondisi && in_array($pinkel->status, ['L', 'R', 'H'])) {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                }

                $tgl_akhir = new DateTime($tgl_kondisi);
                $tgl_awal = new DateTime($pinkel->tgl_cair);
                $selisih = $tgl_akhir->diff($tgl_awal);
                $selisih = $selisih->y * 12 + $selisih->m;

                $_kolek = 0;
                if ($wajib_pokok != '0') {
                    $_kolek = $tunggakan_pokok / $wajib_pokok;
                }

                $kolek_bulan = ceil($_kolek + ($selisih - $angsuran_ke));

                // Tentukan tingkat kolek berdasarkan konfigurasi database (sama dengan kolek_desa)
                $tingkat_kolek = getTingkatKolekCadangan($kolek_bulan, $kolekData);

                // Inisialisasi array kolek untuk baris ini
                $row_kolek = array_fill(0, count($kolekData), 0);
                $row_kolek[$tingkat_kolek] = $saldo_pokok;

                $j_alokasi += $pinkel->alokasi;
                $j_saldo += $saldo_pokok;
                $j_tunggakan_pokok += $tunggakan_pokok;
                $j_tunggakan_jasa += $tunggakan_jasa;
                
                foreach ($row_kolek as $idx => $val) {
                    $j_kolek[$idx] += $val;
                }
            @endphp
        @endforeach

        @if (count($kd_desa) > 0)
            @php
                $j_pross = $j_saldo / $j_alokasi;
                $t_alokasi += $j_alokasi;
                $t_saldo += $j_saldo;
                $t_tunggakan_pokok += $j_tunggakan_pokok;
                $t_tunggakan_jasa += $j_tunggakan_jasa;
                
                foreach ($j_kolek as $idx => $val) {
                    $t_kolek[$idx] += $val;
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
                    <td align="center">f = (kolom selain lancar) / total saldo</td>
                </tr>

                @php
                    $no_urut = 1;
                    $total_saldo = 0;
                    $total_beban = 0;
                    $total_npl = 0;
                    
                    // Hitung total saldo dan beban
                    foreach ($activeKolek as $idx => $kolek) {
                        $nilai_kolek = $t_kolek[$idx] ?? 0;
                        $prosentase = floatval($kolek['prosentase']);
                        $beban = ($nilai_kolek * $prosentase) / 100;
                        $total_saldo += $nilai_kolek;
                        $total_beban += $beban;
                    }
                @endphp

                @foreach ($activeKolek as $idx => $kolek)
                    @php
                        $nilai_kolek = $t_kolek[$idx] ?? 0;
                        $prosentase = floatval($kolek['prosentase']);
                        $beban = ($nilai_kolek * $prosentase) / 100;
                        $npl = $total_saldo > 0 ? ($nilai_kolek / $total_saldo) * 100 : 0;
                    @endphp
                    <tr>
                        <td align="center">{{ $no_urut }}</td>
                        <td>{{ $kolek['nama'] }}</td>
                        <td align="center">{{ $prosentase }}%</td>
                        <td align="right">{{ number_format($nilai_kolek) }}</td>
                        <td align="right">{{ number_format($beban) }}</td>
                        @if ($idx == 0)
                            <td align="center" rowspan="{{ $jumlah_kolek+1 }}">
                                {{ round($total_saldo > 0 ? (($total_saldo - ($t_kolek[0] ?? 0)) / $total_saldo) * 100 : 0, 2) }}%
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
