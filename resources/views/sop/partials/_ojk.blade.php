@php
    // Tampilkan placeholder titik-titik sebagai kosong supaya tidak terlihat
    // seperti data sungguhan di kolom isian.
    $sandi_tampil = trim($kec->sandi_lkm);
    $sandi_tampil = trim($sandi_tampil, '.') === '' ? '' : $kec->sandi_lkm;

    // `saham` bawaan selalu terurut sesuai kolom `urutan` (diisi dari form ini).
    $daftar_saham = App\Models\Saham::where('lokasi', $kec->id)
        ->orderBy('urutan')
        ->orderBy('id')
        ->get();

    // Sisakan 3 baris kosong supayaoperand baru bisa langsung diketik.
    $jumlah_baris = max(count($daftar_saham), 3);
@endphp

<div class="alert alert-info">
    Data di bawah ini dipakai oleh <b>Profil OJK</b> pada semua laporan OJK
    (laporan 20 &amp; 21). Alamat, nama lembaga, telepon, dan email sudah diatur
    di tab <b>Identitas Lembaga</b>.
</div>

<form action="/pengaturan/ojk/{{ $kec->id }}" method="post" id="FormOjk">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-4">
            <div class="position-relative mb-3">
                <label for="sandi_lkm">Nomor Sandi LKM</label>
                <input autocomplete="off" type="text" name="sandi_lkm" id="sandi_lkm"
                    class="form-control" value="{{ $sandi_tampil }}"
                    placeholder="Kosongkan bila belum ada">
                <small class="form-text text-muted">
                    Dikosongkan / diisi titik-titik akan mengabaikan penyimpanan, jadi
                    placeholder lama tidak tertimpa.
                </small>
                <small class="text-danger" id="msg_sandi_lkm"></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="position-relative mb-3">
                <label for="ijin_usaha">No. dan Tanggal Izin Usaha</label>
                <textarea name="ijin_usaha" id="ijin_usaha" class="form-control"
                    rows="2">{{ $kec->ijin_usaha }}</textarea>
                <small class="text-danger" id="msg_ijin_usaha"></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="position-relative mb-3">
                <label for="dasar_catat">Dasar Pencatatan</label>
                <input autocomplete="off" type="text" name="dasar_catat" id="dasar_catat"
                    class="form-control" value="{{ $kec->dasar_catat }}">
                <small class="text-danger" id="msg_dasar_catat"></small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="position-relative mb-3">
                <label for="desa_kec">Kelurahan/Desa</label>
                <input autocomplete="off" type="text" name="desa_kec" id="desa_kec"
                    class="form-control" value="{{ $kec->desa_kec }}">
                <small class="text-danger" id="msg_desa_kec"></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="position-relative mb-3">
                <label for="kode_pos">Kode Pos</label>
                <input autocomplete="off" type="text" name="kode_pos" id="kode_pos"
                    class="form-control" value="{{ $kec->kode_pos }}">
                <small class="text-danger" id="msg_kode_pos"></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="position-relative mb-3">
                <label for="provinsi">Provinsi</label>
                <input autocomplete="off" type="text" name="provinsi" id="provinsi"
                    class="form-control" value="{{ $kec->provinsi }}">
                <small class="text-danger" id="msg_provinsi"></small>
            </div>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Pemegang Saham, Direksi &amp; Komisaris</h6>
        <button type="button" class="btn btn-sm btn-light" id="TambahBarisSaham">
            <i class="fa-solid fa-plus"></i> Tambah Baris
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle" id="TabelSaham">
            <thead class="table-light">
                <tr>
                    <th style="width:40px" class="text-center">#</th>
                    <th style="min-width:220px">Pemegang Saham</th>
                    <th style="min-width:150px">Rupiah (Rp)</th>
                    <th style="min-width:120px">Persentase (%)</th>
                    <th style="min-width:200px">Nama Direksi</th>
                    <th style="min-width:170px">Jabatan Direksi</th>
                    <th style="min-width:200px">Nama Komisaris</th>
                    <th style="min-width:170px">Jabatan Komisaris</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < $jumlah_baris; $i++)
                    @php $s = $daftar_saham[$i] ?? null; @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                name="saham[{{ $i }}][nama_saham]"
                                value="{{ $s->nama_saham ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-end js-angka"
                                name="saham[{{ $i }}][rp_saham]"
                                value="{{ $s->rp_saham ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-end"
                                name="saham[{{ $i }}][pros_saham]"
                                value="{{ $s->pros_saham ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                name="saham[{{ $i }}][nama_direksi]"
                                value="{{ $s->nama_direksi ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                list="DaftarJabatanDireksi"
                                name="saham[{{ $i }}][jab_direksi]"
                                value="{{ $s->jab_direksi ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                name="saham[{{ $i }}][nama_kom]"
                                value="{{ $s->nama_kom ?? '' }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                list="DaftarJabatanKomisaris"
                                name="saham[{{ $i }}][jab_kom]"
                                value="{{ $s->jab_kom ?? '' }}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 js-hapus-saham"
                                title="Hapus baris">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <datalist id="DaftarJabatanDireksi">
        <option value="Direktur Utama"></option>
        <option value="Direktur"></option>
        <option value="Direktur Operasional"></option>
        <option value="Sekretaris"></option>
        <option value="Bendahara"></option>
    </datalist>
    <datalist id="DaftarJabatanKomisaris">
        <option value="Komisaris Utama"></option>
        <option value="Komisaris"></option>
    </datalist>

    <p class="form-text text-muted mb-0">
        Nama direksi/komisaris menempel pada baris pemegang saham, mengikuti
        format Profil OJK. Baris kosong diabaikan saat disimpan. Total
        persentase dan rupiah dihitung ulang otomatis oleh laporan.
    </p>
</form>

<div class="d-flex justify-content-end">
    <button type="button" id="SimpanOjk" data-target="#FormOjk"
        class="btn btn-sm btn-dark mb-0 btn-simpan">
        Simpan Perubahan
    </button>
</div>

<script>
    (function () {
        const KOLOM = [
            ['nama_saham', ''],
            ['rp_saham', 'text-end js-angka'],
            ['pros_saham', 'text-end'],
            ['nama_direksi', ''],
            ['jab_direksi', 'list="DaftarJabatanDireksi"'],
            ['nama_kom', ''],
            ['jab_kom', 'list="DaftarJabatanKomisaris"'],
        ];

        function barisTemplate(i) {
            // Nama kolom memakai __I__ lalu diganti dengan indeks baris,
            // sehingga tidak bertabrakan dengan interpolasi template literal.
            const cells = KOLOM.map(function (k) {
                return '<td><input type="text" class="form-control form-control-sm ' + k[1] +
                    '" name="saham[__I__][' + k[0] + ']"></td>';
            }).join('');

            return '<tr>' +
                '<td class="text-center js-nomor"></td>' + cells +
                '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-link text-danger p-0 js-hapus-saham"' +
                ' title="Hapus baris"><i class="fa-solid fa-trash"></i></button>' +
                '</td></tr>';
        }

        function nextIndex() {
            let max = -1;
            $('#TabelSaham tbody tr').each(function () {
                const name = $(this).find('input').first().attr('name') || '';
                const m = name.match(/saham\[(\d+)\]/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function renumber() {
            $('#TabelSaham tbody tr').each(function (i) {
                $(this).find('.js-nomor').text(i + 1);
            });
        }

        $(document).on('click', '#TambahBarisSaham', function (e) {
            e.preventDefault();
            const i = nextIndex();
            $('#TabelSaham tbody').append(barisTemplate(i).replace(/__I__/g, i));
            renumber();
        });

        $(document).on('click', '.js-hapus-saham', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            renumber();
        });
    })();
</script>