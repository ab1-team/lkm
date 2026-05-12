@php
    use App\Utils\Tanggal;
    use Carbon\Carbon;
    Carbon::setLocale('id');
    $waktu = date('H:i');
    $tempat = 'Kantor LKM';
    $wt_cair = explode('_', $pinkel->wt_cair);
    if (count($wt_cair) == 1) {
        $waktu = $wt_cair[0];
    }
    if (count($wt_cair) == 2) {
        $waktu = $wt_cair[0];
        $tempat = $wt_cair[1] ?? ' . . . . . . . ';
    }
    $redaksi_spk = str_replace(' <ol> ', '', str_replace(' </ol> ', '', $kec->redaksi_spk));
    $redaksi_spk = str_replace(' <ul> ', '', str_replace(' </ul> ', '', $redaksi_spk));
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
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12pt;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 12pt;">
                    <b> SURAT PERJANJIAN KREDIT (SPK)</b>
                </div>
                <div style="font-size: 12pt;">
                    <b> Perkreditan {{ $pinkel->jpp->nama_jpp }}</b>
                </div>
                <div style="font-size: 12pt;">
                    Nomor: {{ $pinkel->spk_no }}
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"> </td>
        </tr>
    </table>
    <div class="centered-text">
        Dengan memohon rahmat Tuhan Yang Maha Kuasa serta kesadaran akan cita-cita luhur pemberdayaan masyarakat desa untuk
        mencapai kemajuan ekonomi dan kemakmuran bersama, pada hari ini {{ Tanggal::namaHari($pinkel->tgl_cair) }} tanggal
        {{ $keuangan->terbilang(Tanggal::hari($pinkel->tgl_cair)) }} bulan {{ Tanggal::namaBulan($pinkel->tgl_cair) }}
        tahun
        {{ $keuangan->terbilang(Tanggal::tahun($pinkel->tgl_cair)) }}, bertempat di {{ $kec->nama_lembaga_sort }} kami yang
        bertanda
        tangan dibawah ini;
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
        <tr>
            <td width="5"> &nbsp; </td>
            <td width="90"> Nama Lengkap </td>
            <td width="10" align="center"> : </td>
            <td> {{ $dir->namadepan }} {{ $dir->namabelakang }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Jabatan </td>
            <td align="center"> : </td>
            <td> {{ $kec->sebutan_level_1 }} {{ $kec->nama_lembaga_sort }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> NIK </td>
            <td align="center"> : </td>
            <td> {{ $dir->nik }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Alamat </td>
            <td align="center"> : </td>
            <td> {{ $kec->alamat_kec }} </td>
        </tr>
    </table>
    <div class="centered-text">
        Dalam hal ini bertindak untuk dan atas nama Pengurus {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }} selaku pengelola pelayanan
        kredit untuk {{ $pinkel->jpp->deskripsi_jpp }}
        ({{ $pinkel->jpp->nama_jpp }}) di {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }}, Selanjutnya disebut
        <b> Pihak Pertama </b> , dan
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
        <tr>
            <td width="5"> &nbsp; </td>
            <td width="90"> Nama Lengkap </td>
            <td width="10" align="center"> : </td>
            <td> {{ $pinkel->anggota->namadepan }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Jenis kelamin </td>
            <td align="center"> : </td>
            <td> {{ $pinkel->anggota->jk }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Tempat, tangal lahir </td>
            <td align="center"> : </td>
            <td> {{ $pinkel->anggota->tempat_lahir }},
                {{ \Carbon\Carbon::parse($pinkel->anggota->tgl_lahir)->format('d F Y') }}
            </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> NIK </td>
            <td align="center"> : </td>
            <td> {{ $pinkel->anggota->nik }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Berkedudukan di </td>
            <td align="center"> : </td>
            <td> {{ $pinkel->anggota->alamat }} </td>
        </tr>
    </table>
    <div class="centered-text">
        Dalam hubungan ini bertindak untuk dan atas nama diri sendiri yang menjadi bagian tidak terpisahkan dari dokumen
        perjanjian kredit ini, selanjutnya disebut <b>Pihak kedua</b>.
    </div>
    <p class="centered-text">
        Pihak Pertama dan Pihak Kedua dalam kedudukan masing-masing seperti telah diterangkan diatas, Pada hari
        {{ \Carbon\Carbon::parse($pinkel->tgl_cair)->locale('id')->translatedFormat('d F Y') }}
        bertempat di {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }} dengan sadar dan
        sukarela menyatakan telah membuat perjanjian kredit barang kepada <b>Pihak Kedua berupa {{ $pinkel->nama_barang }}.
        </b>Kedua belah pihak sepakat untuk mengikatkan diri dalam perjanjian ini dengan syarat-syarat sebagai berikut:
    </p>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 1 </b><br>
        <b class="centered-text">Perjanjian</b>
        <ol class="centered-text">
            <li>Perjanjian ini dibuat untuk menyepakati ketentuan yang disetujui oleh kedua belah pihak.
            </li>
            <li>
                Perjanjian kredit ini berlaku setelah ditandatanganinya perjanjian ini.
            </li>
        </ol>
    </div>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 2 </b><br>
        <b class="centered-text">Nilai dan Barang</b>
        <ol class="centered-text">
            <li>Barang yang dikreditkan adalah berupa {{ $pinkel->nama_barang }} .</li>
            <li>Nilai barang tersebut diatas sebesar <b> {{ number_format($pinkel->alokasi) }}
                    ({{ $keuangan->terbilang($pinkel->alokasi) }} Rupiah). </b></li>
            <li>Status kepemilikan barang sampai dengan sebelum perjanjian ini dinyatakan berakhir adalah <b>Fidusia atau
                    Sewa Beli.</b></li>
            <li>Perjanjian ini berakhir ketika <b>Pihak Kedua</b> telah selesai melakukan pembayaran sesuai dengan
                kesepakatan.</li>
        </ol>
    </div>
    <br>
    <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 3 </b><br>
        <b class="centered-text">SISTEM PENGEMBALIAN & PEMBAYARAN ANGSURAN </b>
        <ol class="centered-text">
            <li><b> Pihak Kedua </b> wajib membayar utang tersebut kepada <b> Pihak Pertama </b> dengan cara pembayaran
                angsuran
                sebesar
                <b> {{ number_format($pinkel->alokasi) }} ({{ $keuangan->terbilang($pinkel->alokasi) }} Rupiah) </b>
                ditambah
                jasa <b> {{ $pinkel->pros_jasa / $pinkel->jangka }} % Flat </b> sebesar
                <b> {{ number_format($pinkel->alokasi * ($pinkel->pros_jasa / $pinkel->jangka / 100)) }}
                    ({{ $keuangan->terbilang($pinkel->alokasi * ($pinkel->pros_jasa / $pinkel->jangka / 100)) }} Rupiah)
                </b>
                setiap bulan, selama {{ $pinkel->jangka }} bulan,
                yang dimulai pada {{ Tanggal::namaHari($pinkel->tgl_cair) }},
                {{ \Carbon\Carbon::parse($pinkel->tgl_cair)->translatedFormat('d F Y') }} dan
                sampai target pelunasan, sebagaimana jadwal angsuran terlampir.
            </li>
            <li>Jika Kredit diselesaikan sebelum jangka waktu pengembalian, maka <b>Pihak Kedua</b> diwajibkan
                membayar <b>sisa pokok, jasa pada bulan tersebut ditambah 1x jasa bulan berikutnya sebagai kompensasi.</b> </li>
            <li>
                <b> Pihak Kedua </b> mebayar angsuran pertama dan biaya administrasi Ketika barang datang.
            </li>
            <li>
                Pembayaran angsuran tersebut di lakukan setiap bulan di kantor {{ $kec->nama_lembaga_sort }} yang beralamat
                di {{ $kec->sebutan_kec }}
                {{ $kec->nama_kec }}
            </li>
            <li>
                Kwitansi tanda terima sebagai bukti pembayaran yang sah adalah kwitansi yang di keluarkan oleh <b>Pihak
                    Pertama</b>
                dengan cap dan tanda tangan Asli Petugas {{ $kec->nama_lembaga_sort }}
            </li>

        </ol>
    </div>
    <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 4 </b><br>
        <b class="centered-text">SANKSI KETERLAMBATAN PEMBAYARAN</b>
        <ol class="centered-text">
            <li>
                <b>Pihak Kedua</b> dianggap terlambat membayar jika waktu pembayarannya melewati bulan.
            </li>
            <li>{!! $kec->spk_denda ?? 'Keterlambatan angsuran <b>Pihak Kedua</b> telah melampaui 1(satu) bulan, maka <b>Pihak kedua</b> di bebani denda sebesar <b>2%</b> diakumulasikan dari jumlah tunggakan pada saat jatuh tempo angsuran' !!}</li>
            <li>
                Apabila selama 3 bulan berturut turut <b>Pihak Kedua</b> tidak membayar angsuran, <b>Pihak
                    Pertama</b>,berhak menarik /mengambil barang yang di beli atau barang yang menjadi agunan tujuk sesuai
                dengan nomial harga barang yang di beli.
            </li>
        </ol>
    </div>
    <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 5 </b><br>
        <b class="centered-text">PEMBATALAN</b>
        <ol class="centered-text">
            <li>Dengan tidak dilakukannya pembayaran angsuran oleh <b>Pihak Kedua</b> berturut-turut sesuai dengan Pasal 4
                ayat 3 Surat
                Perjanjian ini maka tanpa memerlukan teguran terlebih dahulu dari <b>Pihak Pertama</b>, telah cukup
                membuktikan
                bahwa <b>Pihak Kedua</b> dalam keadaan lalai atau wan prestasi.</li>
            <li>Keadaan lalai atau wan prestasi tersebut mengakibatkan perjanjian jual – beli ini batal dengan sendirinya
                tanpa diperlukan putusan dari Pengadilan Negeri yang berarti kedua belah pihak telah menyetujui untuk
                melepas segala ketentuan yang telah termuat dalam Pasal 1266 Kitab Undang-Undang Hukum Perdata.</li>
            <li>Dalam hal pembatalan perjanjian ini maka seluruh pembayaran dari <b>Pihak Kedua</b> kepada <b>Pihak
                    Pertama</b> dianggap
                sebagai uang sewa atas pemakaian BARANG {{ $pinkel->jpp->nama_jpp }} tersebut.
            </li>
            <li>
                Selanjutnya <b>Pihak Kedua</b> memberi kuasa penuh kepada <b>Pihak Pertama</b> yang atas kuasanya dengan hak
                substitusi
                untuk mengambil BARANG {{ $pinkel->jpp->nama_jpp }} milik <b>Pihak Pertama</b>, baik yang berada di tempat
                <b>Pihak Kedua</b>
                atau di tempat
                pihak lain yang mendapat hak dari padanya.
            </li>
            <li>
                Apabila diperlukan, <b>Pihak Pertama</b> berhak meminta bantuan pihak yang berwajib untuk melaksanakan
                pengambilan BARANG {{ $pinkel->jpp->nama_jpp }} tersebut dan segala biaya pengambilan barang-barang
                tersebut sepenuhnya
                menjadi beban dan tanggung jawab <b>Pihak Kedua</b>.
            </li>
        </ol>
    </div> <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 6 </b><br>
        <b class="centered-text">PEMINDAH TANGANAN BARANG {{ $pinkel->jpp->nama_jpp }} </b>
        <ol class="centered-text">
            <li>Terhitung sejak tanggal penyerahan BARANG {{ $pinkel->jpp->nama_jpp }}, maka segala resiko yang berkenaan
                dengan BARANG
                {{ $pinkel->jpp->nama_jpp }} tersebut sepenuhnya menjadi tanggung jawab <b>Pihak Kedua</b>.</li>
            <li>Berkenaan dengan masalah tersebut, <b>Pihak Kedua</b> selama masih terikat dalam perjanjian ini dilarang
                melakukan tindakan atau perbuatan yang bertujuan untuk mengalihkan atau memindahtangankan kepemilikan BARANG
                {{ $pinkel->jpp->nama_jpp }}, semisal:
                <ol class="centered-text" type="a">
                    <li>Menjual</li>
                    <li>Menggadaikan</li>
                    <li>Melakukan hal-hal yang bertujuan mengalihkan atau memindahtangankan kepemilikan BARANG
                        {{ $pinkel->jpp->nama_jpp }} lainnya.</li>
                </ol>
            </li>

        </ol>
    </div> <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 7 </b><br>
        <b class="centered-text">KERUSAKAN DAN KEHILANGAN </b>
        <ol class="centered-text">
            <li>Apabila terjadi kerusakan atas BARANG {{ $pinkel->jpp->nama_jpp }} karena pemakaian, maka <b>Pihak
                    Kedua</b> berkewajiban untuk
                memperbaiki atau mengeluarkan ongkos biaya atas kerusakan yang diderita BARANG {{ $pinkel->jpp->nama_jpp }}
                tersebut
                sehubungan dengan pemakaiannya.</li>
            <li>Apabila terjadi kehilangan atas BARANG {{ $pinkel->jpp->nama_jpp }} karena sebab, akibat atau hal-hal
                lainnya, maka <b>Pihak Kedua</b> tetap berkewajiban penuh untuk melakukan pembayaran angsuran sesuai dengan
                ketentuan dalam Pasal 6
                perjanjian ini.</li>
            <li>
                Setelah semua angsuran pembayaran sesuai Pasal 6 perjanjian ini dilunasi <b>Pihak Kedua</b>, hak kepemilikan
                atas BARANG ELEKTRONIK tersebut beralih sepenuhnya kepada <b>Pihak Kedua</b>.
            </li>
        </ol>
    </div> <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 8 </b><br>
        <b class="centered-text">PENYELESAIAN PERSELISAHAN</b>
        <ol class="centered-text">
            <li>Penggunaan Kembali barang oleh <b>Pihak Kedua</b> setelah diterapkanya sanksi pada PASAL 5, dapat dilakukan
                apabila seluruh kewajiban angsuran dan denda di bayar lunas sesuai target angsuran berjalan.</li>
            <li>Apabila terjadi hal hal yang tidak diinginkan yang menyebabkan <b>Pihak Kedua</b> tidak bisa melanjutkan
                angsuran
                atau melunasi kewajhibanya, seperti: meninggal dunia, melarikan diri, berpindah domisili, gangguan kejiwaan,
                sakit parah dll, maka penjamin dan ahli waris bersedia menanggung beban kewajiban sampai lunas.
            </li>
            <li>
                Hal- hal yang tidak diatur dan/atau belum diatur dalam perjanjian ini dan/atau terjadi perbedaan penafsiran
                atas seluruh atau Sebagian dari perjanjian ini maka kedua belah pihak sepakat untuk menyelesaikanya secara
                musyawarah untuk mufakat.
            </li>
            <li>
                Apabila tidak tercapai kata mufakat dalam proses penyelesaian perselisihan sebagaimana di maksud pasal 8 ayat
                3 maka akan di selesaikan secara hukum sesuai hukum yang berlaku di Indonesia melalui pengadilan <strong>Kabupaten {{$kec->kabupaten->nama_kab}}</strong>.
            </li>
        </ol>
    </div> <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 9 </b><br>
        <b class="centered-text">Lain lain</b>
        <div class="centered-text">
            Hal-hal yang belum atau belum cukup diatur dalam perjanjian ini akan diatur lebih lanjut dalam bentuk surat
            menyurat dan atau addendum perjanjian yang ditandatangani oleh para pihak yang merupakan satu kesatuan dan
            bagian yang tidak terpisahkan dari perjanjian ini.

        </div>
    </div> <br>
    <div style="text-align: center;">
        <b class="centered-text"> PASAL 10 </b><br>
        <b class="centered-text">Penutup</b>
        <div class="centered-text">
            Perjanjian Kredit barang ini dibuat rangkap 2 (dua) di atas kertas bermaterai cukup untuk masing-masing pihak
            yang mempunyai kekuatan hukum yang sama dan ditanda tangani oleh kedua belah pihak dalam keadaan sehat jasmani
            dan rohani, serta tanpa unsur paksaan dari pihak manapun.
        </div>
    </div>
    <div style="text-align: center;" style="font-size: 10pt;">
        <br>
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;" class="p">
            <tr>
                <td>
                    {!! $ttd !!}
                </td>
            </tr>
        </table>
        </td>
        </tr>
        </table>
    </div>
@endsection
