@php
    use App\Utils\Tanggal;
    function rupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <!-- Judul -->
    <div style="width:100%; font-size:10pt;">
        <div style="text-align:center; font-size:18pt; font-weight:bold; margin-bottom:5px;">
            SURAT PERNYATAAN
        </div>
    </div>

    <!-- Identitas -->
    <div style="width:100%; font-size:10pt; text-align:justify; margin-bottom:10px;">
        <div style="display:table; width:100%; font-size:10pt; text-align:justify;">
            <div style="display:table-row;">
                <div style="display:table-cell;" colspan="3">Yang bertanda tangan di bawah ini,</div>
            </div>
            <div style="display:table-row;">
                <div style="display:table-cell; width:120;">Nama Lengkap</div>
                <div style="display:table-cell; width:5px; text-align:right;">:</div>
                <div style="display:table-cell;">{{ $pinkel->anggota->namadepan }}</div>
            </div>
            <div style="display:table-row;">
                <div style="display:table-cell;">Jenis Kelamin</div>
                <div style="display:table-cell; text-align:right;">:</div>
                <div style="display:table-cell;">{{ $pinkel->anggota->jk }}</div>
            </div>
            <div style="display:table-row;">
                <div style="display:table-cell;">Tempat, Tgl. lahir</div>
                <div style="display:table-cell; text-align:right;">:</div>
                <div style="display:table-cell;">
                    {{ $pinkel->anggota->tempat_lahir }}
                    {{ Tanggal::tglLatin($pinkel->anggota->tgl_lahir) }}
                </div>
            </div>
            <div style="display:table-row;">
                <div style="display:table-cell;">NIK</div>
                <div style="display:table-cell; text-align:right;">:</div>
                <div style="display:table-cell;">{{ $pinkel->anggota->nik }}</div>
            </div>
            <div style="display:table-row;">
                <div style="display:table-cell;">Alamat</div>
                <div style="display:table-cell; text-align:right;">:</div>
                <div style="display:table-cell;">
                    {{ $pinkel->anggota->alamat }} {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}
                    {{ $pinkel->anggota->d->desa }} {{ $kec->sebutan_kec }} {{ $kec->nama_kec }}
                    {{ $nama_kabupaten }}
                </div>
            </div>
            <div style="display:table-row;">
                <div style="display:table-cell;">Pekerjaan</div>
                <div style="display:table-cell; text-align:right;">:</div>
                <div style="display:table-cell;">
					{{is_numeric($pinkel->anggota->usaha) ? $pinkel->anggota->u->nama_usaha : $pinkel->anggota->usaha}}
                </div>
            </div>
        </div>
        <div style="margin-top:10px;">
            Dengan ini menyatakan dengan sebenarnya dan pernyataan ini tidak dapat ditarik kembali, bahwa:
        </div>
    </div>

    <!-- Daftar Pernyataan -->
    <div style="width:100%; font-size:10pt; display:block; break-inside:auto; page-break-inside:auto;">
        <ol>
            <li>
                Saya selaku Nasabah {{ $kec->nama_lembaga_sort }} menyatakan benar-benar telah meminjam uang sebesar Rp. {{ rupiah($pinkel->status == 'P' ? $pinkel->proposal : $pinkel->verifikasi) }},
                dengan jaminan berupa barang sebagai berikut :
                    @php
                        $jaminan = json_decode($pinkel->jaminan, true) ?? $pinkel->jaminan;
                    @endphp
                    
                        <ul style="list-style: disc;">
                                <li>
<table border="0" width="100%" cellspacing="0" cellpadding="5" style="font-size: 10pt; border-collapse: collapse;">
    @if (is_array($jaminan))
        {{-- Pilihan Jaminan --}}
        @if ($jaminan['jenis_jaminan'] == '1')
            <tr><td class="style9" width="100px">Jenis Jaminan</td><td class="style27">: Sertifikat Hak Milik (SHM)</td></tr>
            <tr><td class="style9" width="100px">Nomor Sertifikat</td><td class="style27">: {{ $jaminan['nomor_sertifikat'] }}</td></tr>
            <tr><td class="style9" width="100px">Nama Pemilik</td><td class="style27">: {{ $jaminan['nama_pemilik'] }}</td></tr>
            <tr><td class="style9" width="100px">Alamat</td><td class="style27">: {{ $jaminan['alamat'] }}</td></tr>
            <tr><td class="style9" width="100px">Luas</td><td class="style27">: {{ $jaminan['luas'] }} m²</td></tr>
            <tr><td class="style9" width="100px">Nilai Jual Tanah</td><td class="style27">: {{ rupiah($jaminan['nilai_jual_tanah']) }}</td></tr>
        @elseif ($jaminan['jenis_jaminan'] == '2')
            <tr><td class="style9" width="100px">Jenis Jaminan</td><td class="style27">: BPKB Kendaraan Bermotor</td></tr>
            <tr><td class="style9" width="100px">Nomor</td><td class="style27">: {{ $jaminan['nomor'] }}</td></tr>
            <tr><td class="style9" width="100px">Nama Pemilik</td><td class="style27">: {{ $jaminan['nama_pemilik'] }}</td></tr>
            <tr><td class="style9" width="100px">Nopol</td><td class="style27">: {{ $jaminan['nopol'] }}</td></tr>
            <tr><td class="style9" width="100px">Nilai Jual Kendaraan</td><td class="style27">: {{ rupiah($jaminan['nilai_jual_kendaraan']) }}</td></tr>
        @elseif ($jaminan['jenis_jaminan'] == '3')
            <tr><td class="style9" width="100px">Jenis Jaminan</td><td class="style27">: Surat Kepegawaian</td></tr>
            <tr><td class="style9" width="100px">Nomor</td><td class="style27">: {{ $jaminan['nomor'] }}</td></tr>
            <tr><td class="style9" width="100px">Nama Pegawai</td><td class="style27">: {{ $jaminan['nama_pegawai'] }}</td></tr>
            <tr><td class="style9" width="100px">Instansi Penerbit</td><td class="style27">: {{ $jaminan['nama_kuitansi_penerbit'] }}</td></tr>
        @elseif ($jaminan['jenis_jaminan'] == '4')
            <tr><td class="style9" width="100px">Jenis Jaminan</td><td class="style27">: Dokumen Jaminan</td></tr>
            <tr><td class="style9" width="100px">Nomor Jaminan</td><td class="style27">: {{ $jaminan['nama_jaminan'] }}</td></tr>
            <tr><td class="style9" width="100px">Keterangan</td><td class="style27">: {{ $jaminan['keterangan'] }}</td></tr>
            <tr><td class="style9" width="100px">Nilai Jaminan</td><td class="style27">: {{ rupiah($jaminan['nilai_jaminan']) }}</td></tr>
        @elseif ($jaminan['jenis_jaminan'] == '5')
            <tr><td class="style9" width="100px">Jenis Jaminan</td><td class="style27">: Sertifikat Hak Milik (SHM) Tanah dan Bangunan</td></tr>
            <tr><td class="style9" width="100px">Nomor Sertifikat</td><td class="style27">: {{ $jaminan['nomor_sertifikat'] }}</td></tr>
            <tr><td class="style9" width="100px">Nama Pemilik</td><td class="style27">: {{ $jaminan['nama_pemilik'] }}</td></tr>
            <tr><td class="style9" width="100px">Alamat</td><td class="style27">: {{ $jaminan['alamat'] }}</td></tr>
            <tr><td class="style9" width="100px">Luas</td><td class="style27">: {{ $jaminan['luas'] }} m²</td></tr>
            <tr><td class="style9" width="100px">Nilai Jual Tanah</td><td class="style27">: {{ rupiah($jaminan['nilai_jual_tanah']) }}</td></tr>
        @endif
    @else
        {{-- Fallback jika $jaminan bukan array (string biasa) --}}
        <tr>
            <td height="12" width="100px">Nama barang</td>
            <td width="10" align="center">:</td>
            <td><b>{{ $jaminan }}</b></td>
        </tr>
        <tr>
            <td height="12" width="100px">Nilai Jual</td>
            <td width="10" align="center">:</td>
            <td><b>Rp. _______________________</b></td>
        </tr>
    @endif
</table>
                            @for ($i = 0; $i < 3; $i++)
                                <li>
                                    <div style="display:table; width:100%; font-size:10pt;">
                                        <div style="display:table-row;">
                                            <div style="display:table-cell; width:100px; height:12px;">Nama barang</div>
                                            <div style="display:table-cell; width:10px; text-align:center;">:</div>
                                            <div style="display:table-cell;">
                                                <b>_____________________________________________</b>
                                            </div>
                                        </div>
                                        <div style="display:table-row;">
                                            <div style="display:table-cell; width:100px; height:12px;">Nilai Jual</div>
                                            <div style="display:table-cell; width:10px; text-align:center;">:</div>
                                            <div style="display:table-cell;">
                                                <b>Rp. _______________________</b>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endfor
                        </ul>
            </li>

            <li>Barang yang saya jaminkan tersebut adalah benar-benar milik saya sendiri,</li>

            <li>Saya berkewajiban merawat dan melindungi barang jaminan tersebut dan tidak akan menjual,
                menggadaikan, dan/atau memindahtangankan kepada pihak lain sebelum kredit/pinjaman saya tersebut
                lunas,</li>

            <li>Apabila terjadi kemacetan atas kredit saya tersebut, saya bersedia menyerahkan barang jaminan
                tersebut kepada pihak yang berwenang, guna menyelesaikan kredit/pinjaman saya kepada
                {{ $kec->nama_lembaga_sort }} Kecamatan {{ $kec->nama_kec }},</li>

            <li>Saya berjanji akan mengembalikan pinjaman saya tersebut sesuai dengan peraturan yang ada di
                {{ $kec->nama_lembaga_sort }},</li>

            <li>Apabila di kemudian hari saya melanggar isi dari surat pernyataan ini, maka saya bersedia dilaporkan
                kepada pihak yang berwajib dan/atau diproses secara hukum.</li>

            <li>Jika dikemudian hari terjadi force majeure seperti banjir, gempa bumi, tanah longsor, petir, angin
                topan, kebakaran, huru-hara, kerusuhan, pemberontakan, dan perang atau saya berhalangan tetap
                seperti sakit atau meninggal dunia yang mengakibatkan tidak dapat terpenuhinya kewajiban saya sesuai
                poin 5 (lima) diatas, maka sisa angsuran akan ditanggung oleh ahli waris.</li>
        </ol>
    </div>

    <!-- Penutup -->
    <div style="width:100%; font-size:10pt; margin-top:10px;">
        Demikian surat pernyataan ini saya buat dengan sebenarnya dan dengan penuh kesadaran serta rasa tanggung jawab.
    </div>
    
    <!-- Tanda Tangan -->
    
    <div style="display:table; width:100%; font-size:10pt;">
        <div style="display:table-row;">
            <div style="display:table-cell;" colspan="3" style="height:20px;">&nbsp;</div>
        </div>
        <div style="display:table-row;">
            <div style="display:table-cell; width:33%;">&nbsp;</div>
            <div style="display:table-cell; width:33%;">&nbsp;</div>
            <div style="display:table-cell; width:33%; text-align:center;">
                {{ $kec->nama_kec }}, _________________
            </div>
        </div>
        <div style="display:table-row;">
            <div style="display:table-cell; text-align:center;">Saksi 1</div>
            <div style="display:table-cell; text-align:center;">Saksi 2</div>
            <div style="display:table-cell; text-align:center;">Yang Menyatakan</div>
        </div>
        <div style="display:table-row;">
            <div style="display:table-cell;" colspan="3" style="height:80px;">&nbsp;</div>
        </div>
        <div style="display:table-row;">
            <div style="display:table-cell; text-align:center;">
                <b>{{ $dir->namadepan}} {{ $dir->namabelakang}}</b>
            </div>
            <div style="display:table-cell; text-align:center;">
                <b>{{ $pinkel->anggota->penjamin}}</b>
            </div>
            <div style="display:table-cell; text-align:center;">
                <b>{{ $pinkel->anggota->namadepan}}</b>
            </div>
        </div>
    </div>
@endsection
