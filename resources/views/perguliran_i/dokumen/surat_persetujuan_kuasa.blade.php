@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">

        <tr>
            <td height="70" colspan="3" class="bottom">
                <p align="center" class="style6" style="font-size: 18pt; font-weight: bold;">SURAT KUASA KHUSUS</p>

            </td>
        </tr>

        <tr>
            <td height="10" colspan="3" class="style9"></td>
        </tr>
        <tr>
            <td height="10" colspan="3" class="style9">Yang bertanda tangan dibawah ini:</td>
        </tr>
        <tr>
            <td height="10" class="style9">Nama Lengkap </td>
            <td class="style27">: {{ $pinkel->anggota->namadepan }}</td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
        <tr>
            <td height="10" class="style9">Jenis Kelamin </td>
            <td class="style27">: {{ $pinkel->anggota->jk }}</td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
        <tr>
            <td height="10" class="style9">NIK</td>
            <td class="style27">: {{ $pinkel->anggota->nik }}</td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
        <tr>
            <td height="10" class="style9">Tempat,Tanggal Lahir </td>
            <td class="style27">: {{ $pinkel->anggota->tempat_lahir }}
                {{ Tanggal::tglLatin($pinkel->anggota->tgl_lahir) }}
            </td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
        <tr>
            <td width="20%" height="10" class="style9">Alamat</td>
            <td width="42%" class="style27">: {{ $pinkel->anggota->alamat }}
                {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}
                {{ $pinkel->anggota->d->desa }} {{ $kec->sebutan_kec }} {{ $kec->nama_kec }}
                {{ $nama_kabupaten }} </td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
        <tr>
            <td height="10" class="style9">Pekerjaan</td>
            <td class="style27">
                :
				{{is_numeric($pinkel->anggota->usaha) ? $pinkel->anggota->u->nama_usaha : $pinkel->anggota->usaha}}

            </td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>

        <tr>
            <td height="10" class="style9">Nomor HP</td>
            <td class="style27">: {{ $pinkel->anggota->hp }}</td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>


        <tr>
            <td height="20" colspan="3" class="style9">Dengan ini memberikan persetujuan dan kuasa sepenuhnya kepada,
            </td>
        </tr>
        <tr>
            <td height="10" class="style9">Nama Lengkap </td>
            <td class="style27">: {{ $dir->namadepan }} {{ $dir->namabelakang }}</td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
        <tr>
            <td height="10" class="style9">Jabatan </td>
            {{-- <td class="style27">: {{ $dir->j->nama_jabatan }} {{ $kec->nama_lembaga_sort }}</td> --}}
            <td class="style27">: {{ $kec->sebutan_level_1 }} {{ $kec->nama_lembaga_sort }}</td>
            <td height="10" class="style9">&nbsp;</td>
        </tr>
    </table>

    <style>
        .align-justify {
            text-align: justify;
        }
    </style>

    <table>
        <tr>
            <td height="10" colspan="3" class="style9 align-justify">
                <p>
                    Untuk menggunakan barang jaminan yang kami serahterimakan kepada {{ $kec->nama_lembaga_sort }},
                    sebagaimana tertuang dalam bukti serah terima barang jaminan yang menjadi bagian tidak terpisahkan dari
                    dokumen Pencairan Kredit berkaitan dengan permohonan kredit ini.
                    Selanjutnya saya menyatakan sanggup untuk memberikan keterangan, memberikan dukungan dan/atau
                    menandatangani kelengkapan dokumen apabila dikemudian hari diperlukan dalam proses eksekusi barang
                    jaminan dalam rangka memenuhi kewajiban pengembalian kredit saya kepada {{ $kec->nama_lembaga_sort }}.
                    <br> Demikian surat persetujuan/pernyataan sekaligus Surat kuasa khusus ini saya buat secara sadar tanpa
                    tekanan dari pihak manapun serta untuk dapat digunakan sebagaimana mestinya.
                </p>
            </td>
        </tr>

        <tr>
            <td height="20" colspan="2" class="style9"></td>
            <td height="20" class="style9">
                <div align="right"><span class="style9"><br></span></div>
            </td>
        </tr>
    </table>

    <table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">

        <tr>
            <td class="style26">
                <div align="center" class="style9">
                    <p>&nbsp;</p>
                    <p>Penerima Kuasa </p>
                </div>
            </td>
            <td class="style26">
                <div align="center" class="style9">
                    {{ $pinkel->anggota->d->nama_desa }}, {{ Tanggal::tglLatin($pinkel->tgl_proposal) }} <br>
                    <p>Pemberi Kuasa</p>
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
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                @endif
            </td>
            <td colspan="2" align="center">
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
            </td>
        </tr>
        <tr>
            <td align="center" style="font-weight: bold;">
                {{ $dir->namadepan }} {{ $dir->namabelakang }}
            </td>
            <td colspan="2" align="center" style="font-weight: bold;">
                {{ $pinkel->anggota->namadepan }}
            </td>
        </tr>
    </table>
@endsection
