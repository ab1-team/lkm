@php
    use App\Utils\Keuangan;
    use App\Utils\Tanggal;
    $keuangan = new Keuangan();
    $section = 0;
    $empty = false;
@endphp

@extends('pelaporan.layout.base')

@section('content')

<style type="text/css">
    .style6 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: bold;
        -webkit-print-color-adjust: exact;
    }

    .style9 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        -webkit-print-color-adjust: exact;
    }

    .style10 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        -webkit-print-color-adjust: exact;
    }

    .top {
        border-top: 1px solid #000000;
    }

    .bottom {
        border-bottom: 1px solid #000000;
    }

    .left {
        border-left: 1px solid #000000;
    }

    .right {
        border-right: 1px solid #000000;
    }

    .all {
        border: 1px solid #000000;
    }

    .style26 {
        font-family: Arial, Helvetica, sans-serif
    }

    .style27 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        font-weight: bold;
    }

    .align-justify {
        text-align: justify;
    }

    .align-center {
        text-align: center;
    }

    .align-right {
        text-align: right;
    }

    .align-left {
        text-align: left;
    }
</style>

@php
    $nomor = 0;
    
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
@endphp

@foreach ($jenis_pp as $jpp)
    @php
        if ($jpp->pinjaman_individu->isEmpty()) {
            $empty = true;
            continue;
        }
        $nomor++;

        $jumlah_aktif = 0;
        $j_saldo_pokok = 0;
        $t_saldo_pokok = 0;
        $t_alokasi = 0;
        $kd_desa = [];
    @endphp

    @if ($nomor > 1)
    <div class="break"></div>
        @php
            $empty = false;
        @endphp
    @endif

    <table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
        <tr>
            <td height="20" colspan="10" class="bottom"></td>
            <td height="20" colspan="2" class="bottom">
                
            </td>
        </tr>
        <tr>
            <td height="20" colspan="12" class="style6 bottom align-center"><br>DAFTAR RINCIAN PINJAMAN YANG DIBERIKAN
                (Aktif) <br><br></td>
        </tr>
    </table>
                
    <table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
        <tr>
            <td width="20%" class="style9">NAMA LKM</td>
            <td width="70%" class="style9">:{{ $kec->nama_lembaga_long }}</td>
        </tr>
        <tr>
            <td width="20%" class="style9">SANDI LKM</td>
            <td width="70%" class="style9">:{{ $kec->sandi_lkm }}</td>
        </tr>
        <tr>
            <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
            <td width="70%" class="style9 bottom">:{{ $tgl }}</td>
        </tr>
    </table>

    <table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
        <tr align="center" height="30px" class="style9">
            <th width="2%" rowspan="2" class="left bottom">No</th>
            <th width="20%" rowspan="2" class="left bottom">Peminjam - Loan ID</th>
            <th width="10%" rowspan="2" class="left bottom">Jenis Penggunaan</th>
            <th width="7%" rowspan="2" class="left bottom">Periode Pembayaran</th>
            <th colspan="2" class="left bottom">Jangka Waktu</th>
            <th colspan="2" class="left bottom">Suku Bunga</th>
            <th width="3%" rowspan="2" class="left bottom">Plafon</th>
            <th width="7%" rowspan="2" class="left bottom">Baki Debet</th>
            <th width="3%" rowspan="2" class="left bottom">Jumlah Tunggakan (X)</th>
            <th width="3%" rowspan="2" class="left bottom">Kolektibilitas</th>
            <th colspan="2" class="left bottom right">Agunan</th>
        </tr>
        <tr align="center" height="30px" class="style9">
            <th width="5%" class="left bottom">Mulai</th>
            <th width="5%" class="left bottom">Jatuh Tempo</th>
            <th width="5%" class="left bottom">%</th>
            <th width="5%" class="left bottom">Keterangan</th>
            <th width="5%" class="left bottom">Agunan</th>
            <th width="5%" class="left bottom right ">Nilai</th>
        </tr>
        <tr>
            <th colspan="14" class="style27 top left right align-left">NASABAH PENERIMA INDIVIDU</th>
        </tr>

        @php
            $sumalokasi = 0;
            $alokasi = 0;
            $j_alokasi = 0;
            $j_saldo = 0;
        @endphp

        @foreach ($jpp->pinjaman_individu as $pinj_i)
            @php
                $jaminan = json_decode($pinj_i->jaminan, true);
    
                // Check if json_decode was successful and returned an array
                if (is_array($jaminan) && isset($jaminan['jenis_jaminan'])) {
                    if ($jaminan['jenis_jaminan'] == '1') {
                        $agunan = "Tanah";
                        $nilai_agunan = isset($jaminan['nilai_jual_tanah']) ? 'Rp ' . number_format((float)$jaminan['nilai_jual_tanah'], 0, ',', '.') : '-';
                    }
                    elseif ($jaminan['jenis_jaminan'] == '2') {
                        $agunan = "Kendaraan";
                        $nilai_agunan = isset($jaminan['nilai_jual_kendaraan']) ? 'Rp ' . number_format($jaminan['nilai_jual_kendaraan'], 0, ',', '.') : '-';
                    }
                    elseif ($jaminan['jenis_jaminan'] == '3') {
                        $agunan = "Kepegawaian";
                        $nilai_agunan = "-";
                    }
                    elseif ($jaminan['jenis_jaminan'] == '4') {
                        $agunan = $jaminan['nama_jaminan'] ?? '-';
                        $nilai_agunan = isset($jaminan['nilai_jaminan']) ? 'Rp ' . number_format($jaminan['nilai_jaminan'], 0, ',', '.') : '-';
                    }
                    else {
                        $agunan = "Tanah";
                        $nilai_agunan = isset($jaminan['nilai_jual_tanah']) ? 'Rp ' . number_format((float)$jaminan['nilai_jual_tanah'], 0, ',', '.') : '-';
                    }
                } else {
                    // Default values if JSON decode fails
                    $agunan = "Tidak ada data";
                    $nilai_agunan = "-";
                }
    
                $kd_desa[] = $pinj_i->kd_desa;
                $desa = $pinj_i->kd_desa;
            @endphp
            @if (array_count_values($kd_desa)[$pinj_i->kd_desa] <= '1' ) 
                @if ($section != $desa && count($kd_desa)> 1)
                    <tr style="font-weight: bold; border: 1px solid;">
                        <td class="t l b" colspan="8" align="left" height="15">
                            Jumlah {{ $nama_desa }}
                        </td>
                        <td class="t l b" align="right">{{number_format($j_alokasi)}}</td>
                        <td class="t l b" align="right">{{number_format($j_saldo)}}</td>
                        <td colspan="4"class="t l b" align="right"></td>
                    </tr>
                @endif

                <tr>
                    <td class="t l b" align="center"></td>
                    <td class="style27 left top right" colspan="13">
                        {{ $pinj_i->kode_desa }}. {{$pinj_i->nama_desa}}
                    </td>
                </tr>

                @php
                    $kidp = $pinj_i['id'];

                    $nomor = 1;
                    $section = $pinj_i->kd_desa;
                    $nama_desa = $pinj_i->sebutan_desa . ' ' . $pinj_i->nama_desa;
                    $kpros_jasa = number_format($pinj_i['pros_jasa'] - $pinj_i['jangka'],2);

                    $ktgl1 = $pinj_i['tgl_cair'];
                    $kpenambahan = "+".$pinj_i['jangka']." month";
                    $ktgl2 = date('Y-m-d', strtotime($kpenambahan, strtotime($ktgl1)));
                    $kpros_jasa = number_format($pinj_i['pros_jasa']/$pinj_i['jangka'],2);

                    $j_alokasi = 0;
                    $j_saldo = 0;
                @endphp
            @endif

            @php
                $jumlah_aktif += 1;

                $sum_pokok = 0;
                $sum_jasa = 0;
                $saldo_pokok = $pinj_i->alokasi;
                $saldo_jasa = $pinj_i->pros_jasa == 0 ? 0 : $pinj_i->alokasi * ($pinj_i->pros_jasa / 100);
                if ($pinj_i->saldo) {
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
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                $jatuh_tempo = 0;
                if ($pinj_i->target) {
                    $target_pokok = $pinj_i->target->target_pokok;
                    $target_jasa = $pinj_i->target->target_jasa;
                    $wajib_pokok = $pinj_i->target->wajib_pokok;
                    $wajib_jasa = $pinj_i->target->wajib_jasa;
                    $angsuran_ke = $pinj_i->target->angsuran_ke;
                    $jatuh_tempo = $pinj_i->target->jatuh_tempo;
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                if ($tunggakan_pokok < 0) {
                    $tunggakan_pokok = 0;
                }
                $tunggakan_jasa = $target_jasa - $sum_jasa;
                if ($tunggakan_jasa < 0) {
                    $tunggakan_jasa = 0;
                }

                $pross = $saldo_pokok == 0 ? 0 : $saldo_pokok / $pinj_i->alokasi;

                if ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'L') {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                } elseif ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'R') {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                } elseif ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'H') {
                    $tunggakan_pokok = 0;
                    $tunggakan_jasa = 0;
                    $saldo_pokok = 0;
                    $saldo_jasa = 0;
                }

                $tgl_akhir = new DateTime($tgl_kondisi);
                $tgl_awal = new DateTime($pinj_i->tgl_cair);
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

                // Logika penentuan kolektibilitas dari JSON
                $keterangan = null;
                $matched = false;
                
                foreach ($kolek_items as $idx => $item) {
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
                        $keterangan = $item['nama'];
                        $matched = true;
                        break;
                    }
                }

                // Jika tidak ada yang cocok, ambil kategori terakhir
                if (!$matched && count($kolek_items) > 0) {
                    $last_item = end($kolek_items);
                    $keterangan = $last_item['nama'];
                }

                // Jika pinjaman sudah lunas
                if ($pinj_i->tgl_lunas <= $tgl_kondisi && ($pinj_i->status == 'L' || $pinj_i->status == 'H' || $pinj_i->status == 'R')) {
                    $kolek_bulan = 0;
                    $keterangan = $kolek_items[0]['nama'] ?? 'Lancar';
                }
            @endphp

            <tr align="right" height="15px" class="style9">
                <td class="left top" align="center">{{ $nomor++ }}</td>
                <td class="left top" align="left">{{ $pinj_i->namadepan }} -{{$pinj_i->id}}</td>
                <td class="left top" align="left">Pinjaman Modal Kerja</td>
                <td class="left top" align="center">{{$pinj_i->angsuran_pokok->nama_sistem}}</td>
                <td class="left top" align="center">{{ Tanggal::tglIndo($pinj_i->tgl_cair) }}</td>
                <td class="left top" align="center">{{ Tanggal::tglIndo($ktgl2)}}</td>
                <td class="left top">{{$kpros_jasa}}%</td>
                <td class="left top" align="center">per bulan</td>
                <td class="left top">{{number_format($pinj_i->alokasi)}}</td>
                <td class="left top">{{ number_format($saldo_pokok) }}</td>
                <td class="left top">{{$kolek_bulan}}</td>
                <td class="left top " align="left">{{$keterangan}}</td>
                <td class="left top">{{$agunan}}</td>
                <td class="left top right" align="right">{{$nilai_agunan}}</td>
            </tr>

            @php
                $j_alokasi += $pinj_i->alokasi;
                $j_saldo += $saldo_pokok;
            @endphp
            
            @php
                $t_alokasi += $pinj_i->alokasi;
                $t_saldo_pokok += $saldo_pokok;
            @endphp
        @endforeach

        @if (count($kd_desa) > 0)
        <tr style="font-weight: bold; border: 1px solid;">
            <td class="t l b" colspan="8" align="left" height="15">
                Jumlah {{ $nama_desa }}
            </td>
            <td class="t l b" align="right">{{number_format($j_alokasi)}}</td>
            <td class="t l b" align="right">{{number_format($j_saldo)}}</td>
            <td colspan="4"class="t l b" align="right"></td>
        </tr>

        <tr class="style9">
            <th colspan="8" class="left top" align="center"style="background:rgba(0,0,0, 0.3);">TOTAL KESELURUHAN({{$jumlah_aktif}} Anggota)</th>
            <th class="left top" align="right">{{number_format($t_alokasi)}}</th>
            <th class="left top" align="right">{{number_format($t_saldo_pokok)}}</th>
            <th colspan="4" class="left right top" align="right"></th>
        </tr>

        <tr class="style9">
            <th colspan="14" class="top" align="center">&nbsp;</th>
        </tr>

        <tr>
            <td class="style10 top" colspan="14"><b>Keterangan</b> : Data yang ditampilkan diatas merupakan Individu aktif pada tahun berjalan {{$tahun}}, untuk menampilkan data Individu aktif tahun lalu dapat memilih mode tahun lalu {{ $tahun - 1 }}.</td>
        </tr>

        <tr>
            <td class="style10 top" colspan="14">
                <b>Kolektibilitas</b> : 
                @foreach ($kolek_items as $idx => $item)
                    {{ $item['nama'] }} ({{ $item['durasi'] }} {{ $item['satuan'] }})@if ($idx < count($kolek_items) - 1), @endif
                @endforeach
            </td>
        </tr>
        @endif
    </table>

    <table class="p" border="0" align="center" width="96%" cellspacing="0" cellpadding="0" style="font-size: 12px;"> 
        <tr>
            <td colspan="16">
                <div style="margin-top: 14px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>
    </table>
@endforeach

@endsection
