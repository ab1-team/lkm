@php
    use App\Utils\Keuangan;
    $keuangan = new Keuangan();
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        ol,
        ul {
            margin-left: unset;
        }
    </style>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>CATATAN ATAS LAPORAN KEUANGAN</b>
                </div>
                <div style="font-size: 18px; text-transform: uppercase;">
                    <b>{{ $kec->nama_lembaga_sort }}</b>
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

    <ol style="list-style: upper-alpha;">
    
    @if(in_array(session('lokasi'), [2, 351, 352, 353, 354]))
    
        <li>
            <div style="text-transform: uppercase;">Gambaran Umum</div>
            <div style="text-align: justify">
                Lembaga Koperasi Arthamari adalah lembaga ekonomi beranggotakan masyarakat desa yang dibentuk untuk meningkatkan kesejahteraan melalui prinsip gotong royong, kekeluargaan, dan partisipasi bersama.
            </div> <br>
            <div style="text-align: justify">
                Regulasi atau Dasar Hukum  {{ $kec->nama_lembaga_sort }} adalah sebagai berikut : 
            </div>
            <ol>
                <li>
                    Undang-Undang Nomor 25 Tahun 1992 tentang Perkoperasian
                </li>
                <li>
                    Peraturan Pemerintah Nomor 4 Tahun 1994 tentang Persyaratan dan Tata cara Pengesahan Akta Pendirian dan Perubahan Anggaran Dasar Koperasi. 
                </li>
                <li>
                    Peraturan Pemerintah Nomor 17 Tahun 1994 tentang Pembubaran Koperasi Oleh Pemerintah.
                </li>
                <li>
                    Peraturan Menteri Koperasi dan UKM Nomor 13 Tahun 2023. 
                </li>
                <li>
                    Peraturan Menteri Koperasi dan UKM Nomor 8 Tahun 2023.
                </li>
                <li>
                    Peraturan Pemerintah Nomor 7 Tahun 2021 tentang Kemudahan, Pelindungan dan Pemberdayaan Koperasi dan Usaha Mikro, Kecil dan Menengah
                </li>
                
                <li>
                    Peraturan Menteri Koperasi dan Usaha Kecil dan Menengah (Permenkop UKM) Nomor 72 Tahun 2017
                    <ol style="list-style: lower-alpha">
                        <li>
                            Permenkop 9 Tahun 2018 adalah Peraturan Menteri Koperasi dan UKM Nomor 9 Tahun 2018
                        </li>
                        <li>
                            Permenkop No. 15 Tahun 2015 adalah Peraturan Menteri Koperasi dan Usaha Kecil dan Menengah tentang Usaha Simpan Pinjam oleh Koperasi.
                        </li>
                        <li>
                            Permenkop No 19 Tahun 2015 tentang Rapat Anggota Tahunan
                        </li>
                        <li>
                            Peraturan Menteri Koperasi dan Usaha Kecil dan Menengah Nomor 9 Tahun 2020 tentang Pengawasan Koperasi
                        </li>
                        <li>
                            Peraturan Menteri Koperasi dan Usaha Kecil dan Menengah Nomor 8 Tahun 2023 (Permenkop 8/2023) mengatur tentang usaha simpan pinjam oleh koperasi.
                        </li>
                        <li>
                            Peraturan Menteri Koperasi dan Usaha Kecil dan Menengah Nomor 2 Tahun 2024 tentang Kebijakan Akuntansi Koperasi
                        </li>
                        <li>
                            Permenkop No. 1 Tahun 2025 tentang "Penyaluran Pinjaman atau Pembiayaan Dana Bergulir kepada Koperasi Percontohan (Mock Up) Koperasi Desa/Kelurahan Merah Putih". 
                        </li>
                    </ol>
                </li>
            </ol>
            <p style="text-align: justify">
                Selanjutnya {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
                {{ $kec->nama_kec }}
                telah resmi mendaftar sebagai lembaga keuangan yang selanjutnya mendapat legalitas dari Kementerian Hukum
                dan HAM
                Nomor: {{ $kec->nomor_bh }}.
            </p>
        </li>
    @else
        <li>
            <div style="text-transform: uppercase;">Gambaran Umum</div>
            <div style="text-align: justify">
                Lembaga Keuangan Mikro ({{ $kec->nama_lembaga_sort }}) adalah lembaga keuangan yang khusus didirikan untuk
                memberikan jasa
                pengembangan usaha dan pemberdayaan masyarakat, baik melalui pinjaman atau pembiayaan dalam usaha
                skala mikro kepada masyarakat, pengelolaan simpanan, maupun
                pengembangan usaha yang tidak semata-mata mencari keuntungan.
            </div> <br>
            <div style="text-align: justify">
                Dalam rangka memenuhi amanat regulasi tentang pendirian dan operasional lembaga kuangan mikro (LKM) sebagai
                berikut:
            </div>
            <ol>
                <li>
                    Undang-Undang Nomor 1 Tahun 2013 Tentang Lembaga Keuangan Mikro (Undang-Undang LKM).
                </li>
                <li>
                    Peraturan Pemerintah Nomor 89 Tahun 2014 tentang Suku Bunga Pinjaman Atau Imbal Hasil Pembiayaan dan
                    Luas Cakupan Wilayah Usaha Lembaga Keuangan Mikro.
                </li>
                <li>
                    Surat Edaran Otoritas Jasa Keuangan (SEOJK), SEOJK Nomor 29/SEOJK.05/2015 tentang Laporan Keuangan
                    Lembaga Keuangan Mikro.
                </li>
                <li>
                    Peraturan Otoritas Jasa Keuangan (POJK):
                    <ol style="list-style: lower-alpha">
                        <li>
                            POJK Nomor 12/POJK.05/2014 tentang Perizinan Usaha dan Kelembagaan Lembaga Keuangan Mikro.
                        </li>
                        <li>
                            POJK Nomor 13/POJK.05/2014 tentang Penyelenggaraan Usaha Lembaga Keuangan Mikro.
                        </li>
                        <li>
                            POJK Nomor 14/POJK.05/2014 tentang Pembinaan dan Pengawasan Lembaga Keuangan Mikro.
                        </li>
                        <li>
                            POJK Nomor 61/POJK.05/2015 tentang Perubahan atas Peraturan Otoritas Jasa Keuangan Nomor
                            12/POJK.05/2014 tentang Perizinan Usaha dan Kelembagaan Lembaga Keuangan Mikro.
                        </li>
                        <li>
                            POJK Nomor 62/POJK.05/2015 tentang Perubahan atas Peraturan Otoritas Jasa Keuangan Nomor
                            13/POJK.05/2014 tentang Penyelenggaraan Usaha Lembaga Keuangan Mikro.
                        </li>
                    </ol>
                </li>
            </ol>
            <p style="text-align: justify">
                Selanjutnya {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
                {{ $kec->nama_kec }}
                telah resmi mendaftar sebagai lembaga keuangan yang selanjutnya mendapat legalitas dari Kementerian Hukum
                dan HAM
                Nomor: {{ $kec->nomor_bh }}. Adapun susunan pengurusnya adalah sebagai berikut :

            <table style="margin-top: -10px; margin-left: 15px;">
                <tr>
                    <td style="padding: 0px; 4px;" width="100">{{ $kec->nama_bp_long }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $pengawas ? $pengawas->namadepan . ' ' . $pengawas->namabelakang : '......................................' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0px; 4px;">{{ $kec->sebutan_level_1 }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $dir ? $dir->namadepan . ' ' . $dir->namabelakang : '......................................' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0px; 4px;">{{ $kec->sebutan_level_2 }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $sekr ? $sekr->namadepan . ' ' . $sekr->namabelakang : '......................................' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0px; 4px;">{{ $kec->sebutan_level_3 }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $bend ? $bend->namadepan . ' ' . $bend->namabelakang : '......................................' }}
                    </td>
                </tr>
                {{-- <tr>
                    <td style="padding: 0px; 4px;">Unit Usaha</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">.................................</td>
                </tr> --}}
            </table>
            </p>
    @endif

        <li style="margin-top: 12px;">
            <div style="text-transform: uppercase;">
                Ikhtisar Kebijakan Akutansi
            </div>
            <ol>
                <li>
                    Pernyataan Kepatuhan
                    <ol style="list-style: lower-alpha;">
                        <li>
                            Laporan keuangan disusun menggunakan Standar Akuntansi Keuangan Perusahaan Jasa Keuangan Mikro,
                            sesuai Permenkop No. 02 Tahun 2024.
                        </li>
                        <li>Dasar Penyusunan laporan keuangan adalah SOP penatausahaan dan SOP laporan Keuangan.</li>
                        <li>
                            Dasar penyusunan laporan keuangan adalah biaya historis dan menggunakan asumsi dasar kas basis.
                            Mata uang penyajian yang digunakan untuk menyusun laporan keuangan ini adalah Rupiah.
                        </li>
                    </ol>
                </li>
                <li>
                    Piutang Usaha
                    <div>
                        Piutang usaha disajikan sebesar jumlah alokasi pencairan piutang ditambah nilai resceduling setelah
                        dikurangi komulatif angsuran pada setiap pinjaman dan nilai penghapusan pinjaman yang diputuskan
                        dalam Rapat Direksi dan/atau Keputusan Direktur.
                    </div>
                </li>
                <li>
                    Aset Tetap dan Inventaris dan Aset tak berwujud
                    <ol style="list-style: lower-alpha">
                        <li>
                            Aset tetap dan Inventaris beserta Aset tak berwujud dicatat sebesar biaya perolehannya pada saat
                            aset tersebut secara hukum mulai dimiliki oleh {{ $kec->nama_lembaga_sort }} .
                        </li>
                        <li>
                            Aset tetap beserta Inventaris disusutkan menggunakan metode garis lurus tanpa nilai residu.
                        </li>
                    </ol>
                </li>
                <li>
                    Pengakuan Pendapatan dan Beban
                    <ol style="list-style: lower-alpha;">
                        <li>
                            Jasa piutang masyarakat yang sudah dilakukan pembayaran
                            diakui sebagai pendapatan dan diterbitkan kuitansi pembayaran,
                            sedangkan jasa yang seharusnya sudah memasuki kewajiban bayar/target bayar
                            akan tetapi tidak dipenuhi oleh nasabah (menunggak) tetap diakui sebagai pendapatan
                            meskipun tidak diterbitkan kuitansi, sehinga sekaligus dicatatkan sebagai
                            piutang jasa. Adapun berkaitan dengan penerimaan denda atas keterlambatan
                            pembayaran/pinalti diakui sebagai pendapatan pada saat diterbitkan kuitansi pembayaran.
                        </li>
                        <li>
                            Adapun kewajiban bayar atas kebutuhan operasional, pemasaran maupun non operasional pada suatu
                            periode operasi tertentu sebagai akibat telah menikmati manfaat/menerima fasilitas, maka hal
                            tersebut sudah wajib diakui sebagai beban meskipun belum diterbitkan kuitansi pembayaran.
                        </li>
                    </ol>
                </li>
                <li>
                    Pajak Penghasilan
                    <div>
                        Pajak Penghasilan mengikuti ketentuan perpajakan yang berlaku di Indonesia.
                    </div>
                </li>
            </ol>
        </li>

        <li style="margin-top: 12px;">
            <div style="text-transform: uppercase;">
                Informasi Tambahan Laporan Keuangan
            </div>
            <div>
                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed; word-wrap: break-word;">
                    <tr>
                        <td colspan="3" height="5"></td>
                    </tr>
                    <tr style="background: #000; color: #fff;">
                        <td width="15%">Kode</td>
                        <td width="55%">Nama Akun</td>
                        <td width="30%" align="right">Saldo</td>
                    </tr>
                    <tr>
                        <td colspan="3" height="2"></td>
                    </tr>

                    @foreach ($akun1 as $lev1)
                        @php
                            $sum_akun1 = 0;
                        @endphp
                        <tr style="background: rgb(74, 74, 74); color: #fff;">
                            <td height="20" colspan="3" align="center">
                                <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                            </td>
                        </tr>
                        @foreach ($lev1->akun2 as $lev2)
                            <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                                <td>{{ $lev2->kode_akun }}.</td>
                                <td colspan="2">{{ $lev2->nama_akun }}</td>
                            </tr>

                            @foreach ($lev2->akun3 as $lev3)
                                @php
                                    $sum_saldo = 0;
                                    $akun_lev4 = [];
                                @endphp

                                @foreach ($lev3->rek as $rek)
                                    @php
                                        $saldo = $keuangan->komSaldo($rek);
                                        if ($rek->kode_akun == '3.2.02.01') {
                                            $saldo = $keuangan->laba_rugi($tgl_kondisi);
                                        }

                                        $sum_saldo += $saldo;

                                        $akun_lev4[] = [
                                            'kode_akun' => $rek->kode_akun,
                                            'nama_akun' => $rek->nama_akun,
                                            'saldo' => $saldo,
                                        ];
                                    @endphp
                                @endforeach

                                @php
                                    if ($lev1->lev1 == '1') {
                                        $debit += $sum_saldo;
                                    } else {
                                        $kredit += $sum_saldo;
                                    }

                                    $sum_akun1 += $sum_saldo;
                                @endphp

                                <tr style="background: rgb(200,200,200);">
                                    <td>{{ $lev3->kode_akun }}.</td>
                                    <td>{{ $lev3->nama_akun }}</td>
                                    @if ($sum_saldo < 0)
                                        <td align="right">({{ number_format($sum_saldo * -1, 2) }})</td>
                                    @else
                                        <td align="right">{{ number_format($sum_saldo, 2) }}</td>
                                    @endif
                                </tr>

                                @foreach ($akun_lev4 as $lev4)
                                    @php
                                        $bg = 'rgb(230, 230, 230)';
                                        if ($loop->iteration % 2 == 0) {
                                            $bg = 'rgba(255, 255, 255)';
                                        }
                                    @endphp
                                    <tr style="background: rgb(255,255,255);">
                                        <td>{{ $lev4['kode_akun'] }}.</td>
                                        <td>{{ $lev4['nama_akun'] }}</td>
                                        @if ($lev4['saldo'] < 0)
                                            <td align="right">({{ number_format($lev4['saldo'] * -1, 2) }})</td>
                                        @else
                                            <td align="right">{{ number_format($lev4['saldo'], 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach

                        <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                            <td height="20" colspan="2" align="left">
                                <b>Jumlah {{ $lev1->nama_akun }}</b>
                            </td>
                            <td align="right">{{ number_format($sum_akun1, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" height="2"></td>
                        </tr>
                    @endforeach
                    <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                        <td height="20" colspan="2" align="left">
                            <b>Jumlah Liabilitas + Ekuitas </b>
                        </td>
                        <td align="right">{{ number_format($kredit, 2) }}</td>
                    </tr>
                    @if (round($kredit, 2) != round($debit, 2))
                        <tr style="color: rgb(250, 0, 0); font-weight: bold;">
                            <td height="20" colspan="3" align="left">
                                <b>Terdapat selisih antara Aset dengan Liabilitas + Ekuitas sebesar
                                    {{ number_format($debit - $kredit, 2) }}</b>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </li>
        <li style="margin-top: 12px;">
            <div style="text-transform: uppercase;">
                Ketentuan Pembagian SHU :
            </div>
            <div>
                Pembagian laba yang diperoleh dalam satu tahun buku dialokasikan
                untuk
                :
            </div>
            <ol>
                <li>
                    Penambahan modal {{ $kec->nama_lembaga_sort }}/ laba ditahan
                </li>
                <li>
                    Dividen
                </li>
                <li>
                    Alokasi lain yang diputuskan dalam rapat pertangung jawaban dan/atau Rapat Anggota Tahunan (RAT).
                </li>
            </ol>
        </li>

        @if ($keterangan)
            <li style="margin-top: 12px;">
                <div style="text-transform: uppercase;">
                    Lain Lain
                </div>
                <div style="text-align: justify">
                    {!! $keterangan->catatan !!}.
                </div>
            </li>
        @endif

        <li style="margin-top: 12px;">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr>
                    <td align="justify">
                        <div style="text-transform: uppercase;">
                            Penutup
                        </div>
                        <div style="text-align: justify">
                            Catatan atas Laporan Keuangan (CaLK) ini merupakan bagian tidak terpisahkan dari Laporan
                            Keuangan {{ $kec->nama_lembaga_sort }} untuk Laporan Operasi Bulan {{ $nama_tgl }}.
                            Selanjutnya Catatan
                            atas Laporan Keuangan ini diharapkan untuk dapat berguna bagi pihak-pihak yang berkepentingan
                            (stakeholders) serta memenuhi prinsip-prinsip transparansi, akuntabilitas, pertanggungjawaban,
                            independensi, dan fairness dalam pengelolaan keuangan {{ $kec->nama_lembaga_sort }}.
                        </div>

                        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;"
                            class="p">
                            <tr>
                                <td>
                                    <div style="margin-top: 16px;"></div>
                                    {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </li>
    </ol>
@endsection
