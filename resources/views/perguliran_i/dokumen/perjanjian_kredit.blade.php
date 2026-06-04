@php
    use App\Utils\Tanggal;
    use Carbon\Carbon;

    $jaminan = json_decode($pinkel->jaminan, true);
    Carbon::setLocale('id');
    $waktu = date('H:i');
    $tempat = 'Kantor';
    $wt_cair = explode('_', $pinkel->wt_cair);
    if (count($wt_cair) == 1) {
        $waktu = $wt_cair[0];
    }
    if (count($wt_cair) == 2) {
        $waktu = $wt_cair[0];
        $tempat = $wt_cair[1] ?? ' . . . . . . . ';
    }

    $redaksi_spk = '';
    if ($kec->redaksi_spk) {
        $redaksi_spk = str_replace('<ol>', '', str_replace('</ol>', '', $kec->redaksi_spk));
        $redaksi_spk = str_replace('<ul>', '', str_replace('</ul>', '', $redaksi_spk));
    }
@endphp
@extends('perguliran_i.dokumen.layout.base')
@section('content')
    <style>
        /* styles.css */
        .centered-text {
            font-size: 10pt;
            text-align: center;
            text-align: justify;
        }
    </style>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11pt;">
        <br>
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 12pt;">
                    <b> SURAT PERJANJIAN KREDIT (SPK) </b>
                </div>
                <div style="font-size: 11pt;">
                    Nomor:
                    {{ $pinkel->spk_no }}/SPK.{{ $pinkel->jpp->nama_jpp }}-{{ $pinkel->jpp->id }}/BUMDESMA/II/{{ date('Y') }}
                </div>

                <div style="font-size: 10pt;">
                    Tanggal: {{ \Carbon\Carbon::parse($pinkel->tgl_cair)->locale('id')->translatedFormat('d F Y') }}
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"> </td>
        </tr>
    </table>
    <div class="centered-text">
        Dengan memohon rahmat Tuhan Yang Maha Kuasa serta kesadaran akan cita-cita luhur pemberdayaan masyarakat desa untuk
        mencapai kemajuan ekonomi dan kemakmuran bersama, saya yang bertanda tangan di bawah ini :
    </div>
    <div style="text-align: center;">
        <ol class="centered-text" style="list-style-type: upper-roman;">
            <li><b>{{ $dir->namadepan }} {{ $dir->namabelakang }}</b>
                dalam
                hal ini bertindak dalam
                kedudukan selaku Direktur dari <b>Badan Usaha Milik Desa Bersama {{ $kec->nama_lembaga_sort }}
                    {{ $kec->sebutan_kec }}
                    {{ $kec->nama_kec }}</b> oleh
                karena itu bertindak untuk dan atas nama Badan Usaha Milik Desa Bersama {{ $kec->nama_lembaga_sort }},
                berkedudukan
                di Jalan {{ $kec->alamat_kec }} {{ $kec->sebutan_kec }}
                {{ $kec->nama_kec }} Kabupaten {{ $nama_kab }}.</li> <br>
            <li>
                <b> {{ $pinkel->anggota->namadepan }}</b>, Nomor Induk Kependudukan {{ $pinkel->anggota->nik }} bertempat
                tinggal di
                {{ $pinkel->anggota->alamat }} dalam hal ini bertindak untuk
                diri sendiri, selanjutnya disebut DEBITUR.
            </li>
        </ol>
    </div>

    <div class="centered-text">Badan Usaha Milik Desa Bersama {{ $kec->nama_lembaga_sort }} dan DEBITUR dalam kedudukan
        masing-
        masing seperti telah diterangkan di atas pada hari ini
        : {{ \Carbon\Carbon::parse($pinkel->tgl_cair)->translatedFormat('l') }} tanggal :
        {{ $keuangan->terbilang(Tanggal::hari($pinkel->tgl_cair)) }}
        bulan {{ $keuangan->terbilang(Tanggal::bulan($pinkel->tgl_cair)) }}
        tahun {{ $keuangan->terbilang(Tanggal::tahun($pinkel->tgl_cair)) }}
        {{ \Carbon\Carbon::parse($pinkel->tgl_cair)->locale('id')->translatedFormat('d F Y') }}
        jam {{ $pinkel->wt_cair }} bertempat di Kantor BUM Desa
        {{ $kec->nama_lembaga_sort }}
        {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }} , dengan
        sadar dan sukarela menyatakan telah membuat perjanjian utang piutang dengan ketentuan-ketentuan yang disepakati
        bersama sebagai berikut :</div> <br>
    <div style="text-align: center;">
        <b class="centered-text">PASAL 1</b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt; margin-top: 0;">Ayat 1</h3>
        <div class="centered-text">
            Badan Usaha Milik Desa Bersama {{ $kec->nama_lembaga_sort }} setuju memberikan kredit kepada DEBITUR sebesar

            <b>Rp. {{ number_format($pinkel->alokasi) }} ({{ $keuangan->terbilang($pinkel->alokasi) }} Rupiah)</b>
            berdasarkan
            permohonan dari DEBITUR sesuai surat permohonan kredit tanggal
            {{ \Carbon\Carbon::parse($pinkel->tgl_proposal)->locale('id')->translatedFormat('d F Y') }}
            .
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 2 </i> </h3>
        <div class="centered-text">
            DEBITUR, mengaku telah menerima uang dalam jumlah sebagaimana yang diterangkan pada ayat 1 di atas, yang
            mana telah dibayarkan sesuai dengan permohonan dan dibuktikan secara sah dengan bukti kwitansi terlampir,
            yang berlaku sebagai surat pengakuan utang.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 3 </i> </h3>
        <div class="centered-text">
            DEBITUR, menyerahkan jaminan atas pinjaman tersebut pada ayat 1 kepada Badan Usaha Milik Desa Bersama TIRTA
            PESONA JAYA LKD berupa @if ($jaminan['jenis_jaminan'] == '1')
                Nomor Sertifikat: {{ $jaminan['nomor_sertifikat'] ?? 0 }},
                Nama jaminan: {{ $jaminan['nama_pemilik'] ?? 0 }},
                Alamat : {{ $jaminan['alamat'] ?? 0 }} Luas: {{ $jaminan['luas'] ?? 0 }} (m²),
                Nilai Jual Tanah: {{ number_format((float)($jaminan['nilai_jual_tanah'] ?? 0)) }},
            @elseif ($jaminan['jenis_jaminan'] == '2')
                Nomor: {{ $jaminan['nomor'] ?? 0 }},
                Nama jaminan: {{ $jaminan['jenis_kendaraan'] ?? 0 }},
                Nopol: {{ $jaminan['nopol'] ?? 0 }},
                Nilai Jual Kendaraan: {{ number_format($jaminan['nilai_jual_kendaraan'] ?? 0) }},
            @elseif ($jaminan['jenis_jaminan'] == '3')
                Nomor: {{ $jaminan['nomor'] ?? 0 }},
                Nama Pegawai: {{ $jaminan['nama_pegawai'] ?? 0 }},
                Nama Instansi Penerbit: {{ $jaminan['nama_kuitansi_penerbit'] ?? 0 }},
            @elseif ($jaminan['jenis_jaminan'] == '4')
                Nomor Jaminan: {{ $jaminan['nama_jaminan'] ?? 0 }},
                Keterangan: {{ $jaminan['keterangan'] ?? 0 }},
                Nilai Jaminan: {{ number_format($jaminan['nilai_jaminan'] ?? 0) }},
            @else
                Nomor Sertifikat: {{ $jaminan['nomor_sertifikat'] ?? 0 }},
                Nama jaminan: {{ $jaminan['nama_pemilik'] ?? 0 }},
                Alamat : {{ $jaminan['alamat'] ?? 0 }} Luas: {{ $jaminan['luas'] ?? 0 }} (m²),
                Nilai Jual Tanah: {{ number_format((float)($jaminan['nilai_jual_tanah'] ?? 0)) }},
            @endif atas nama{{ $pinkel->anggota->namadepan }} (peminjam) yang terletak di
            {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}
            {{ $pinkel->anggota->d->nama_desa }} Kecamatan {{ $pinkel->anggota->d->nama_kec }},.
        </div>
    </div>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 2 </b>
        <div class="centered-text">
            Kedua belah pihak secara suka rela menerima syarat-syarat perjanjian utang piutang sebagai mana dinyatakan dalam
            ketentuan-ketentuan di bawah ini :
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 1 </i> </h3>
        <div class="centered-text">
            Fasilitas Kredit tersebut akan digunakan untuk modal kerja. DEBITUR bertanggung jawab mengenai kebenaran
            atas penggunaan Fasilitas Kredit tersebut.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 2 </i> </h3>

        <div class="centered-text">
            Atas kredit tersebut dikenakan jasa 1 % (satu per seratus) flat per bulan.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 3 </i> </h3>

        <div class="centered-text">
            Kredit akan dibayarkan kembali dengan sistem bulanan dalam jangka waktu {{ $pinkel->jangka }} bulan, dengan
            jasa <b> {{ $pinkel->pros_jasa / $pinkel->jangka }} % Flat </b> sebesar
            <b>Rp. {{ number_format($pinkel->alokasi * ($pinkel->pros_jasa / $pinkel->jangka / 100)) }}
                ({{ $keuangan->terbilang($pinkel->alokasi * ($pinkel->pros_jasa / $pinkel->jangka / 100)) }} Rupiah)
            </b> setiap tanggal 07 (tujuh),
            sebagaimana jadwal angsuran terlampir.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 4 </i> </h3>

        <div class="centered-text">
            DEBITUR mengerti dan menyadari bahwa jasa pinjaman yang dibayar akan dipergunakan untuk biaya pelayanan dan
            pengelolaan yang sehat serta pemupukan modal yang bermanfaat bagi Badan Usaha Milik Desa Bersama TIRTA
            PESONA JAYA LKD.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 5 </i> </h3>

        <div class="centered-text">
            Apabila DEBITUR membayar angsuran dalam jumlah dan waktu yang tidak sesuai pada jadwal angsuran (terlambat
            satu bulan) maka akan diberikan sanksi/denda sejumlah 1,5 % (satu koma lima per seratus) per bulan.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 6 </i> </h3>
        <div class="centered-text">
            Apabila kemudian hari DEBITUR tidak bisa menepati perjanjian ini kepada Badan Usaha Milik Desa Bersama TIRTA
            PESONA JAYA LKD maka :
            <ol class="centered-text">
                <li>Akan diselesaikan secara musyawarah mufakat antara kedua belah pihak;</li>
                <li>
                    Apabila musyawarah mufakat tidak atau belum ditemukan penyelesaiannya maka Badan Usaha Milik Desa
                    Bersama {{ $kec->nama_lembaga_sort }} berhak menyita dan menjual jaminan pinjaman tersebut pada Pasal 1
                    Ayat 3
                    dari DEBITUR, guna menutup semua pinjaman yang diterimanya.
                </li>
            </ol>
        </div>
    </div>
    <div style="text-align: center;">

        <b class="centered-text"> PASAL 3 </b>
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt; margin-top: 0;"> Ayat
            1</h3>
        <div class="centered-text">
            Pihak kedua dan pemberi kuasa sadar dan mengerti bahwa mengembalikan kredit secara lancar sesuai jadwal yang
            disepakati, merupakan kewajiban hukum sekaligus menunjukkan budi pekerti luhur. Pengembalian kredit secara
            lancar akan memperluas kesempatan untuk memperoleh kredit berikutnya serta membuka peluang bagi orang lain
            mendapatkan giliran pelayanan.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 2 </i> </h3>

        <div class="centered-text">
            Apabila terjadi silang selisih berkenaan dengan hak serta kewajiban yang timbul atas perjanjian utang piutang
            ini, akan diselesaikan secara musyawarah untuk mencapai kata sepakat. Apabila tidak dapat dicapai kata sepakat
            kedua belah pihak setuju untuk menunjuk pengadilan Negeri Kebumen sebagai upaya hukum menyelesaikan
            persengketaan tersebut.
        </div>
    </div>
    <div style="text-align: center;">
        <h3 class="fa fa-align-center" aria-hidden="true" style="font-size: 10pt;"> Ayat 3 </i> </h3>

        <div class="centered-text">
            DEBITUR menyatakan secara sadar dan sukarela telah menandatangani akad perjanjian kredit ini, setelah terlebih
            dahulu membacakan isi perjanjian ini dengan sejelas-jelasnya dan tidak menyatakan keberatan.
        </div>
    </div>
    <br>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;" class="p">
        <tr>
            <td>
                {!! $ttd !!}
            </td>
        </tr>
    </table>
    </div>
@endsection
