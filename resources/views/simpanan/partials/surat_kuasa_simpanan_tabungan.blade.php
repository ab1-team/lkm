@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <style>
        .align-justify {
            text-align: justify;
        }
        .no-break {
            page-break-inside: avoid;
        }
    </style>

    <table width="97%" border="0" align="center" cellpadding="3" cellspacing="0" style="font-size: 9pt;">

        <tr class="no-break">
            <td height="30" colspan="3" class="bottom" style="padding-bottom: 0;">
                <p align="center" class="style6" style="font-size: 18pt; font-weight: bold; margin: 0;">SURAT KUASA</p>
                <p align="center" class="style6" style="font-size: 13pt; font-weight: bold; margin: 0;">PENCAIRAN TABUNGAN UNTUK PENYELESAIAN KEWAJIBAN KREDIT</p>
                <p align="center" class="style9" style="font-size: 10pt; margin: 2px 0 0 0;">
                    &nbsp;
                </p>
            </td>
        </tr>
        <br>
        <tr class="no-break">
            <td colspan="3" class="style9">Yang bertanda tangan di bawah ini:</td>
        </tr>
        <br>
        <tr class="no-break">
            <td width="25%" class="style9" style="white-space:nowrap;">Nama</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->anggota->namadepan }}</td>
            <td class="style9">&nbsp;</td>
        </tr>
        <tr class="no-break">
            <td class="style9" style="white-space:nowrap;">Tempat/Tanggal Lahir</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->anggota->tempat_lahir }},
                {{ Tanggal::tglLatin($simpanan->anggota->tgl_lahir) }}
            </td>
            <td class="style9">&nbsp;</td>
        </tr>
        <tr class="no-break">
            <td class="style9" style="white-space:nowrap;">NIK</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->anggota->nik }}</td>
            <td class="style9">&nbsp;</td>
        </tr>
        <tr class="no-break">
            <td class="style9" style="white-space:nowrap;">Alamat</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->anggota->alamat_anggota }}
                {{ $simpanan->anggota->d->sebutan_desa->sebutan_desa }}
                {{ $simpanan->anggota->d->nama_desa }}
                {{ $kec->sebutan_kec }} {{ $kec->nama_kec }}
                {{ $nama_kabupaten }}
            </td>
            <td class="style9">&nbsp;</td>
        </tr>
        <tr class="no-break">
            <td class="style9" style="white-space:nowrap;">No. CIF/Nasabah</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->id }}</td>
            <td class="style9">&nbsp;</td>
        </tr>
        <tr class="no-break">
            <td class="style9" style="white-space:nowrap;">No. Rekening Tabungan</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->nomor_rekening ?? '-' }}</td>
            <td class="style9">&nbsp;</td>
        </tr>
        <tr class="no-break">
            <td class="style9" style="white-space:nowrap;">No. Perjanjian Kredit</td>
            <td class="style27" style="padding-left:14px;text-indent:-14px;">: {{ $simpanan->id }}</td>
            <td class="style9">&nbsp;</td>
        </tr>
        <br>
        <tr>
            <td colspan="3" class="style9 align-justify" style="text-indent: 30px;">
                Selanjutnya disebut sebagai <b>PEMBERI KUASA</b>.
                Dengan ini memberikan kuasa kepada: <b>{{ $kec->nama_lembaga_sort }}</b> beralamat di
                {{ $kec->alamat_kec }} {{ $kec->nama_kec }} {{ $nama_kabupaten }}.
                Dalam hal ini diwakili oleh pejabat yang berwenang sesuai ketentuan internal perusahaan,
                selanjutnya disebut sebagai <b>PENERIMA KUASA</b>.
            </td>
        </tr>
        <br>
        <tr>
            <td colspan="3" class="style9"><b>KHUSUS</b></td>
        </tr>
        <tr>
            <td colspan="3" class="style9 align-justify" style="text-indent: 30px;">
                Pemberi Kuasa dengan ini memberikan kuasa yang tidak dapat dicabut kembali selama masih
                terdapat kewajiban kredit (sepanjang diperbolehkan oleh ketentuan hukum yang berlaku) kepada
                {{ $kec->nama_lembaga_sort }} untuk:
            </td>
        </tr>
        <tr>
            <td colspan="3" class="style9 align-justify">
                <ol>
                    <li>
                        Mencairkan, mendebet, atau memindahbukukan sebagian atau seluruh saldo tabungan atas nama
                        Pemberi Kuasa yang tersimpan pada {{ $kec->nama_lembaga_sort }}.
                    </li>
                    <li>
                        Menggunakan dana tersebut untuk pembayaran kewajiban Pemberi Kuasa yang meliputi:
                        <ul>
                            <li>Pokok pinjaman sebesar Rp {{ number_format($pinkel->proposal ?? 0, 0, ',', '.') }};</li>
                            <li>Bunga pinjaman sebesar {{ $pinkel->pros_jasa ?? 0 }}%;</li>
                            <li>Biaya administrasi atau biaya lain yang menjadi kewajiban Pemberi Kuasa
                                berdasarkan Perjanjian Kredit.</li>
                        </ul>
                    </li>
                    <li>
                        Menandatangani dokumen administrasi yang diperlukan dalam pelaksanaan pencairan atau
                        pendebetan tabungan tersebut.
                    </li>
                    <li>
                        Menerbitkan bukti transaksi, kuitansi, atau dokumen pelunasan yang berkaitan dengan
                        penyelesaian kewajiban kredit Pemberi Kuasa.
                    </li>
                </ol>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="style9 align-justify">
                <b>Pemberi Kuasa menyatakan bahwa:</b>
                <ol>
                    <li>
                        Saldo tabungan yang digunakan berdasarkan surat kuasa ini merupakan milik sah
                        Pemberi Kuasa.
                    </li>
                    <li>
                        Pemberi Kuasa mengetahui dan menyetujui bahwa penggunaan saldo tabungan dilakukan
                        semata-mata untuk mengurangi atau melunasi kewajiban kreditnya pada
                        {{ $kec->nama_lembaga_sort }}.
                    </li>
                    <li>
                        Apabila saldo tabungan tidak mencukupi untuk melunasi seluruh kewajiban kredit,
                        maka sisa kewajiban tetap menjadi tanggung jawab Pemberi Kuasa.
                    </li>
                    <li>
                        Apabila terdapat kelebihan dana setelah seluruh kewajiban kredit diselesaikan,
                        maka sisa saldo tetap menjadi hak Pemberi Kuasa dan dicatat sesuai ketentuan
                        yang berlaku.
                    </li>
                    <li>
                        Pemberi Kuasa membebaskan {{ $kec->nama_lembaga_sort }} dari segala tuntutan hukum
                        sepanjang tindakan pencairan atau pendebetan dilakukan sesuai isi surat kuasa ini
                        dan Perjanjian Kredit.
                    </li>
                </ol>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="style9 align-justify" style="text-indent: 30px;">
                Surat Kuasa ini merupakan bagian yang tidak terpisahkan dari Perjanjian Kredit antara
                Pemberi Kuasa dengan {{ $kec->nama_lembaga_sort }} dan berlaku sejak tanggal ditandatangani
                sampai seluruh kewajiban kredit Pemberi Kuasa dinyatakan lunas atau surat kuasa ini berakhir
                berdasarkan ketentuan hukum yang berlaku.
            </td>
        </tr>
        <tr>
            <td colspan="3" class="style9 align-justify" style="text-indent: 30px;">
                Demikian Surat Kuasa ini dibuat dengan sebenar-benarnya dalam keadaan sadar, sehat jasmani
                dan rohani, tanpa adanya paksaan dari pihak mana pun untuk dipergunakan sebagaimana mestinya.
            </td>
        </tr>
    </table>

    <table width="97%" border="0" align="center" cellpadding="3" cellspacing="0" style="font-size: 9pt; page-break-before: always;">
        <tr>
            <td class="style26">
                <div align="center" class="style9">
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">Penerima Kuasa</p>
                    <p style="margin: 0;">{{ $kec->nama_lembaga_sort }}</p>
                </div>
            </td>
            <td class="style26">
                <div align="center" class="style9">
                    {{ $kec->nama_kec }},
                    {{ Tanggal::tglLatin(date('Y-m-d')) }} <br>
                    <p style="margin: 0;">Pemberi Kuasa</p>
                </div>
            </td>
        </tr>
        <tr>
            <td align="center">
                @php
                    $logoPath = storage_path('app/public/qr/' . session('lokasi') . '.jpeg');
                @endphp

                @if (file_exists($logoPath))
                    <img src="../storage/app/public/qr/{{ session('lokasi') }}.jpeg" height="70"
                        alt="{{ $kec->id }}">
                @else
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 0;">&nbsp;</p>
                @endif
            </td>
            <td colspan="2" align="center">
                <p style="margin: 0;">&nbsp;</p>
                <p style="margin: 0;">&nbsp;</p>
                <p style="margin: 0;">&nbsp;</p>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-weight: bold;">
                {{ $dir->namadepan }} {{ $dir->namabelakang }}
            </td>
            <td colspan="2" align="center" style="font-weight: bold;">
                {{ $simpanan->anggota->namadepan }}
            </td>
        </tr>
        <tr>
            <td align="center">
                {{ $kec->sebutan_level_1 ?? '' }} {{ $kec->nama_lembaga_sort }}
            </td>
            <td colspan="2" align="center">&nbsp;</td>
        </tr>
    </table>
@endsection
