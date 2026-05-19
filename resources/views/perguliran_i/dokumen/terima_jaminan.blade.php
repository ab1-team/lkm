@php
    use App\Utils\Tanggal;
    use Carbon\Carbon;
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
    $jaminan  = json_decode($pinkel->jaminan, true);

    function rupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')

<table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">        
    <tr>
        <td colspan="3" align="center" style="font-size: 18pt; font-weight: bold; padding: 20px 0;">
            SURAT TANDA TERIMA DOKUMEN
        </td>
    </tr>    

    <tr>
        <td colspan="3" class="style9">
            Sehubungan dengan <strong>Surat Perjanjian Kredit (SPK) Nomor: {{ $pinkel->spk_no }}</strong>, {{ Tanggal::namaHari($pinkel->tgl_cair) }} tanggal
        {{ $keuangan->terbilang(Tanggal::hari($pinkel->tgl_cair)) }} bulan {{ Tanggal::namaBulan($pinkel->tgl_cair) }}
        tahun
        {{ $keuangan->terbilang(Tanggal::tahun($pinkel->tgl_cair)) }}, bertempat di {{ $kec->nama_lembaga_sort }} kami yang
        bertanda
        tangan dibawah ini:
        </td>
    </tr>

    <tr>
        <td width="5">&nbsp;</td>
        <td class="style9" width="25%">Nama Lengkap</td>
        <td class="style27">: {{ $login->namadepan }}</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="style9">NIK</td>
        <td class="style27">: {{ strval($login->nik) }}</td>

    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="style9">Jabatan</td>
        <td class="style27">: {{ $login->j->nama_jabatan }} {{ $kec->nama_lembaga_sort }}</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="style9">Alamat</td>
        <td class="style27">: {{ $login->alamat }}</td>
    </tr>

    {{-- Pilihan Jaminan --}}
    @if ($jaminan['jenis_jaminan'] == '1')
        <tr> 
            <td colspan="3" class="style9" style="padding-top: 10px;">
                Dengan ini menyatakan telah menerima secara utuh dokumen jaminan berupa <strong>Sertifikat Hak Milik (SHM)</strong> dengan rincian:
            </td>
        </tr>
        <tr><td>&nbsp;</td><td class="style9">Nomor Sertifikat</td><td class="style27">: {{ $jaminan['nomor_sertifikat'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nama Pemilik</td><td class="style27">: {{ $jaminan['nama_pemilik'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Alamat</td><td class="style27">: {{ $jaminan['alamat'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Luas</td><td class="style27">: {{ $jaminan['luas'] }} m²</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nilai Jual Tanah</td><td class="style27">: {{ rupiah($jaminan['nilai_jual_tanah']) }}</td></tr>

    @elseif ($jaminan['jenis_jaminan'] == '2')
        <tr>
            <td colspan="3" class="style9" style="padding-top: 10px;">
                Dengan ini menyatakan telah menerima secara utuh dokumen jaminan berupa <strong>BPKB Kendaraan Bermotor</strong> dengan rincian:
            </td>
        </tr>
        <tr><td>&nbsp;</td><td class="style9">Nomor</td><td class="style27">: {{ $jaminan['nomor'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nama Pemilik</td><td class="style27">: {{ $jaminan['nama_pemilik'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nopol</td><td class="style27">: {{ $jaminan['nopol'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nilai Jual Kendaraan</td><td class="style27">: {{ rupiah($jaminan['nilai_jual_kendaraan']) }}</td></tr>

    @elseif ($jaminan['jenis_jaminan'] == '3')
        <tr>
            <td colspan="3" class="style9" style="padding-top: 10px;">
                Dengan ini menyatakan telah menerima secara utuh dokumen jaminan berupa <strong>Surat Kepegawaian</strong> dengan rincian:
            </td>
        </tr>
        <tr><td>&nbsp;</td><td class="style9">Nomor</td><td class="style27">: {{ $jaminan['nomor'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nama Pegawai</td><td class="style27">: {{ $jaminan['nama_pegawai'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Instansi Penerbit</td><td class="style27">: {{ $jaminan['nama_kuitansi_penerbit'] }}</td></tr>

    @elseif ($jaminan['jenis_jaminan'] == '4')
        <tr>
            <td colspan="3" class="style9" style="padding-top: 10px;">
                Dengan ini menyatakan telah menerima secara utuh dokumen jaminan berupa <strong>Dokumen Jaminan</strong> dengan rincian:
            </td>
        </tr>
        <tr><td>&nbsp;</td><td class="style9">Nomor Jaminan</td><td class="style27">: {{ $jaminan['nama_jaminan'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Keterangan</td><td class="style27">: {{ $jaminan['keterangan'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nilai Jaminan</td><td class="style27">: {{ rupiah($jaminan['nilai_jaminan']) }}</td></tr>

   @elseif ($jaminan['jenis_jaminan'] == '5')
        <tr>
            <td colspan="3" class="style9" style="padding-top: 10px;">
                Dengan ini menyatakan telah menerima secara utuh dokumen jaminan berupa <strong>Sertifikat Hak Milik (SHM) Tanah dan Bangunan</strong> dengan rincian:
            </td>
        </tr>
        <tr><td>&nbsp;</td><td class="style9">Nomor Sertifikat</td><td class="style27">: {{ $jaminan['nomor_sertifikat'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nama Pemilik</td><td class="style27">: {{ $jaminan['nama_pemilik'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Alamat</td><td class="style27">: {{ $jaminan['alamat'] }}</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Luas</td><td class="style27">: {{ $jaminan['luas'] }} m²</td></tr>
        <tr><td>&nbsp;</td><td class="style9">Nilai Jual Tanah</td><td class="style27">: {{ rupiah($jaminan['nilai_jual_tanah']) }}</td></tr>
    @endif

    <tr>
        <td colspan="3" class="style9" style="padding-top: 15px;">
            Dokumen tersebut diterima untuk dijadikan sebagai agunan dalam perjanjian sebagaimana dimaksud dalam SPK di atas.  
            <br><br>
            Demikian surat tanda terima ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya serta memiliki kekuatan hukum.
        </td>
    </tr>
</table>

<br>

<table width="97%" border="0" align="center" cellpadding="3" cellspacing="0" style="table-layout: fixed;">
    <tr>
        <td width="50%">&nbsp;</td>
        <td width="50%" align="center" class="style9">
            {{ $kab->nama_kab }}, {{ Tanggal::tglLatin($pinkel->tgl_cair) }} 
        </td>
    </tr>
    <tr>
        <td align="center">
            Yang Menyerahkan,
            <br><br><br><br><br><br>
            <strong>{{ $pinkel->anggota->namadepan }}</strong>
        </td>
        <td align="center">
            Yang Menerima,
            <br><br><br><br><br><br>
            <strong>{{ $login->namadepan }}</strong>
        </td>
    </tr>
</table>

@endsection
