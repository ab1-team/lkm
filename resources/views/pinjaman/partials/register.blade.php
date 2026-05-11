<style>
    .app-title {
        background-color: #c0c4c5;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
.app-wrapper {
        display: flex;
        align-items: center;
    }
.app-heading {
        display: flex;
        align-items: center;
    }
.app-bg-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: #c0c4c505;
        border-radius: 10%;
        margin-right: 15px;
    }
.app-text_fount {
        font-size: 14px;
        color: #373636;
        margin-top: 15px;
    }

    .custom-button {
        width: 200px;
        float: right;
        /* Tempatkan tombol di sebelah kanan */
        margin: 20px;
        padding: 10px;
        text-align: center;
        background-color: #343a40;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .custom-button:hover {
        background-color: #495057;
    }
</style>

<div class="card-body">
    <div class="app-title">
        <div class="app-wrapper">
            <div class="app-heading">
                <div class="app-bg-icon fa-solid fa-file-circle-plus"> </div>
                <div class="app-text_fount">
                    <h5><b>Register Proposal {{ $kelompok?->jenis_produk_pinjaman != '3' ? 'Kelompok' : 'Usaha' }}
                            {{ $kelompok?->nama_kelompok }}</b></h5>
                    <div>
                        {{ $kelompok?->d->sebutan_desa->sebutan_desa }} {{ $kelompok?->d->nama_desa }},
                        <b>{{ $kelompok?->d->kd_desa }}</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="/perguliran" method="post" id="FormRegisterProposal">
        @csrf

        <input type="hidden" name="id_kel" id="id_kel" value="{{ $kelompok?->id }}">
        <div class="row">
            <div class="col-md-3">
                <div class="position-relative mb-3">
                    <label for="tgl_proposal" class="form-label">Tgl Proposal</label>
                    <input autocomplete="off" type="text" name="tgl_proposal" id="tgl_proposal"
                        class="form-control date" value="{{ date('d/m/Y') }}">
                    <small class="text-danger" id="msg_tgl_proposal"></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="position-relative mb-3">
                    <label for="pengajuan" class="form-label">Pengajuan Rp.</label>
                    <input autocomplete="off" type="text" name="pengajuan" id="pengajuan" class="form-control">
                    <small class="text-danger" id="msg_pengajuan"></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="position-relative mb-3">
                    <label for="jangka" class="form-label">Jangka</label>
                    <input autocomplete="off" type="number" name="jangka" id="jangka" class="form-control"
                        value="{{ $kec->def_jangka }}">
                    <small class="text-danger" id="msg_jangka"></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="position-relative mb-3">
                    <label for="pros_jasa" class="form-label">Prosentase Jasa (%)</label>
                    <input autocomplete="off" type="number" name="pros_jasa" id="pros_jasa" class="form-control"
                        value="{{ $kec->def_jasa }}">
                    <small class="text-danger" id="msg_pros_jasa"></small>
                </div>
            </div>
        </div>

        @php
            $class1 = 'col-md-6';
            $class2 = 'col-md-6';
            $class3 = 'col-md-6';
            if ($kelompok?->jenis_produk_pinjaman == '3') {
                $class1 = 'col-md-2';
                $class2 = 'col-md-5';
                $class3 = 'col-md-5';
            }
        @endphp

        <div class="row">
            <div class="{{ $class1 }}">
                <div class="position-relative mb-3">
                    <label class="form-label" for="jenis_jasa">Jenis Jasa</label>
                    <select class="js-example-basic-single form-control" name="jenis_jasa" id="jenis_jasa"
                        style="width: 100%;">
                        @foreach ($jenis_jasa as $jj)
                            <option value="{{ $jj->id }}">
                                {{ $jj->nama_jj }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="msg_jenis_jasa"></small>
                </div>
            </div>
            <div class="{{ $class1 }} {{ $kelompok?->jenis_produk_pinjaman == '3' ? 'd-none' : '' }}">
                <div class="position-relative mb-3">
                    <label class="form-label" for="jenis_produk_pinjaman">Jenis Produk Pinjaman</label>
                    <select class="js-example-basic-single form-control" name="jenis_produk_pinjaman"
                        id="jenis_produk_pinjaman" style="width: 100%;">
                        @foreach ($jenis_pp as $jpp)
                            <option {{ $jenis_pp_dipilih == $jpp->id ? 'selected' : '' }} value="{{ $jpp->id }}">
                                {{ $jpp->nama_jpp }} ({{ $jpp->deskripsi_jpp }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="msg_jenis_produk_pinjaman"></small>
                </div>
            </div>

            <div class="{{ $class2 }}">
                <div class="position-relative mb-3">
                    <label class="form-label" for="sistem_angsuran_pokok">Sistem Angs. Pokok</label>
                    <select class="js-example-basic-single form-control" name="sistem_angsuran_pokok"
                        id="sistem_angsuran_pokok" style="width: 100%;">
                        @foreach ($sistem_angsuran as $sa)
                            <option value="{{ $sa->id }}">
                                {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="msg_sistem_angsuran_pokok"></small>
                </div>
            </div>
            <div class="{{ $class3 }}">
                <div class="position-relative mb-3">
                    <label class="form-label" for="sistem_angsuran_jasa">Sistem Angs. Jasa</label>
                    <select class="js-example-basic-single form-control" name="sistem_angsuran_jasa"
                        id="sistem_angsuran_jasa" style="width: 100%;">
                        @foreach ($sistem_angsuran as $sa)
                            <option value="{{ $sa->id }}">
                                {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="msg_sistem_angsuran_jasa"></small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="text-center fw-bold">
                    Struktur {{ $kelompok?->jenis_produk_pinjaman != '3' ? 'Kelompok' : 'Lembaga Usaha' }}
                </div>
                <div class="row">
                    @if ($kelompok?->jenis_produk_pinjaman != '3')
                        <div class="col-md-4">
                            <div class="position-relative mb-3">
                                <label for="ketua" class="form-label">Ketua</label>
                                <input autocomplete="off" type="text" name="ketua" id="ketua" class="form-control"
                                    value="{{ $kelompok?->ketua }}">
                                <small class="text-danger" id="msg_ketua"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="position-relative mb-3">
                                <label for="sekretaris" class="form-label">Sekretaris</label>
                                <input autocomplete="off" type="text" name="sekretaris" id="sekretaris"
                                    class="form-control" value="{{ $kelompok?->sekretaris }}">
                                <small class="text-danger" id="msg_sekretaris"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="position-relative mb-3">
                                <label for="bendahara" class="form-label">Bendahara</label>
                                <input autocomplete="off" type="text" name="bendahara" id="bendahara"
                                    class="form-control" value="{{ $kelompok?->bendahara }}">
                                <small class="text-danger" id="msg_bendahara"></small>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <div class="position-relative mb-3">
                                <label for="pimpinan" class="form-label">Pimpinan</label>
                                <input autocomplete="off" type="text" name="pimpinan" id="pimpinan"
                                    class="form-control" value="{{ $kelompok?->ketua }}">
                                <small class="text-danger" id="msg_pimpinan"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative mb-3">
                                <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                <input autocomplete="off" type="text" name="penanggung_jawab" id="penanggung_jawab"
                                    class="form-control" value="{{ $kelompok?->sekretaris }}">
                                <small class="text-danger" id="msg_penanggung_jawab"></small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <button type="submit" id="SimpanProposal" class="btn btn-dark btn-sm custom-button">Simpan Proposal</button>
    <br><br><br>
</div>

<script>
    $('.date').datepicker({
        dateFormat: 'dd/mm/yy'
    });

    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            theme: 'bootstrap4',
        });
    });

    $("#pengajuan").maskMoney();
</script>
