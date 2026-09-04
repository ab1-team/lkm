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
                    <b> SURAT KUASA MENJUAL </b>
                </div>
                {{-- <div style="font-size: 11pt;">
                    Nomor: {{ $pinkel->spk_no }}
                </div> --}}
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"> </td>
        </tr>
    </table>
    <div class="centered-text">
        Yang bertanda tangan di bawah ini, Saya :
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
            <td> Tempat, tanggal lahir </td>
            <td align="center"> : </td>
            <td> {{ $pinkel->anggota->tempat_lahir }},
                {{ \Carbon\Carbon::parse($pinkel->anggota->tgl_lahir)->locale('id')->translatedFormat('d F Y') }}
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
            <td> Alamat </td>
            <td align="center"> : </td>
            <td> {{ $pinkel->anggota->alamat }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td colspan="3">Selanjutnya disebut sebagai Pihak PERTAMA (Pemberi Kuasa)</td>
        </tr>
    </table> <br>
    <div class="centered-text">
        Memberi Kuasa kapada :
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
            <td> Tempat, tanggal lahir </td>
            <td align="center"> : </td>
            <td> {{ $dir->tempat_lahir }},
                {{ \Carbon\Carbon::parse($dir->tgl_lahir)->locale('id')->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td> Jabatan </td>
            <td align="center"> : </td>
            <td> {{ $dir->j->nama_jabatan }} {{ $kec->nama_lembaga_sort }}
                {{ $kec->sebutan_kec }}
                {{ $kec->nama_kec }}</td>
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
            <td> {{ $dir->alamat }} </td>
        </tr>
        <tr>
            <td width="5"> &nbsp; </td>
            <td colspan="3">Selanjutnya disebut sebagai Pihak KEDUA (Penerima Kuasa)</td>
        </tr>
    </table>
    <br>
    <div class="centered-text">
        Sesuai Surat Perjanjian Kredit (SPK) Nomor :
        <b>{{ $pinkel->spk_no }}/SPK.{{ $pinkel->jpp->nama_jpp }}-{{ $pinkel->jpp->id }}/BUMDESMA/II/{{ date('Y') }}</b>
        tanggal
        {{ \Carbon\Carbon::parse($pinkel->tgl_cair)->locale('id')->translatedFormat('d F Y') }}
        PIHAK PERTAMA
        memberikan kuasa sepenuhnya kepada BUMDesa Bersama {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }} melalui PIHAK KEDUA untuk
        melakukan penjualan hak milik saya berupa
        @if ($jaminan['jenis_jaminan'] == '1')
            Nomor Sertifikat: {{ $jaminan['nomor_sertifikat'] ?? 0 }},
            Nama jaminan: {{ $jaminan['nama_pemilik'] ?? 0 }},
            Alamat : {{ $jaminan['alamat'] ?? 0 }} Luas: {{ $jaminan['luas'] ?? 0 }} (m²),
            Nilai Jual Tanah: {{ number_format($jaminan['nilai_jual_tanah'] ?? 0) }},
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
        @endif atas nama <b>{{ $pinkel->anggota->penjamin }}
            ({{ $pinkel->anggota->keluarga->kekeluargaan }})</b> yang terletak di
        {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}
        {{ $pinkel->anggota->d->nama_desa }} Kecamatan {{ $pinkel->anggota->d->nama_kec }},
        sesuai sertifikat yang saya titipkan di BUMDesa Bersama {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }}.
    </div>
    <p class="centered-text">
        Dengan demikian apabila dikemudian hari saya tidak mampu memenuhi kewajiban saya sesuai Surat Perjanjian Kredit
        di atas maka saya tidak berkeberatan apabila hak milik saya berupa tanah tersebut di atas PIHAK KEDUA atau
        Dikuasakan untuk menjual di bawah tangan atau dimuka umum (Lelang) dengan harga yang dikehendaki oleh BUMDesa
        Bersama
        {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
        {{ $kec->nama_kec }} dan hasilnya digunakan untuk melunasi kewajiban pinjaman saya. Apabila hasil
        penjualan tersebut tidak mencukupi sisa pinjaman saya maka saya tetap bertanggungjawab untuk melunasi sisa pinjaman
        tersebut.
    </p>
    <div style="text-align: center;" style="font-size: 10pt;">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;" class="p0">
            <tr>
                <td style="padding: 0pt !important;">
                    <table class="p0" border="0" width="100%" cellspacing="0" cellpadding="0"
                        style="font-size: 10pt;">
                        <br>
                        <tr>
                            <td style="padding: 0pt !important;">
                                <div class="centered-text">
                                    Demikian Surat Kuasa ini Saya buat dengan penuh kesadaran dan tanggung jawab tanpa
                                    adanya tekanan/paksaan dari pihak manapun untuk dapat dipergunakan sebagaimana mestinya
                                    sebagai itikad baik untuk melaksanakan kewajiban saya di BUMDesa Bersama
                                    {{ $kec->nama_lembaga_sort }} {{ $kec->sebutan_kec }}
                                    {{ $kec->nama_kec }}.
                                </div>
                            </td>
                        </tr>
                    </table> <br>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
                        <tr>
                            <td width="40" align="center"> &nbsp; </td>
                            <td width="80" align="center">Yang Menerima Kuasa/ <br>
                                PIHAK KEDUA,</td>
                            <td width="60" align="center"> &nbsp; </td>
                            <td width="50" align="center" colspan="2"> {{ $kec->nama_kec }},
                                {{ Tanggal::tglLatin(date('Y-m-d')) }}<br>
                                Yang Memberi Kuasa/ <br>
                                PIHAK PERTAMA,</td>
                        </tr>
						<tr>
							<td align="center">
								@if ($ttdQrUrl = \App\Utils\QrTtdHelper::inlineDataUri(session('lokasi')))
									<img src="{{ $ttdQrUrl }}" width="150" height="auto" style="height:auto" alt="{{ $kec->id }}">
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
								@if (\App\Utils\QrTtdHelper::displayHasName(session('lokasi')))
									{{ $dir->namadepan }} {{ $dir->namabelakang }}
								@else
									&nbsp;
								@endif
							</td>
							<td colspan="2" align="center" style="font-weight: bold;">
								{{ $pinkel->anggota->namadepan }}
							</td>
						</tr>
                    </table>
                </td>
            </tr>
        </table>
    </div> <br>
    <div style="text-align: center;">
        <div class="centered-text">
            SAKSI-SAKSI :<br>
            <ol class="centered-text">
                <li>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
                        <tr>
                            <td width="80" align="left">{{ $pinkel->anggota->penjamin }}</td>
                            <td width="80" align="right">{{ $pinkel->anggota->keluarga->kekeluargaan }}</td>
                            <td width="80" align="left"> (. . . . . . . . . . . . . . . )</td>
                        </tr>
                    </table>
                </li> <br>
                <li>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10pt;">
                        <tr>
                            <td width="80" align="left">{{ $saksi->namadepan }}</td>
                            <td width="80" align="right">{{ $saksi->j->nama_jabatan }}</td>
                            <td width="80" align="left"> (. . . . . . . . . . . . . . . )</td>
                        </tr>
                    </table>
                </li>
            </ol>
        </div>
    </div>
@endsection
