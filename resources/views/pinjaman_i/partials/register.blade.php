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
                    <h5><b>Register Proposal {{ $anggota->namadepan }}</b></h5>
                    <div>
                        {{ $anggota->d->sebutan_desa->sebutan_desa }} {{ $anggota->d->nama_desa }},
                        <b>{{ $anggota->d->kd_desa }}</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="/perguliran_i" method="post" id="FormRegisterProposal">
        @csrf

        <input type="hidden" name="nia" id="nia" value="{{ $anggota->id }}">
        <div class="row">
            <div class="col-md-3">
                <div class="position-relative mb-3">
                    <label for="tgl_proposal" class="form-label">Tgl proposal</label>
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
        <div class="row">
            <div class="col-md-6">
                <div class="position-relative mb-3">
                    <label for="jenis_jasa" class="form-label">Jenis Jasa</label>
                    <select class="js-example-basic-single form-control" name="jenis_jasa" id="jenis_jasa"
                        style="width: 100%;    ">
                        @foreach ($jenis_jasa as $jj)
                            <option value="{{ $jj->id }}">
                                {{ $jj->nama_jj }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="msg_jenis_jasa"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="position-relative mb-3">
                    <label for="jenis_produk_pinjaman" class="form-label">Jenis Produk Pinjaman</label>
                    <select class="js-example-basic-single form-control " name="jenis_produk_pinjaman"
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
        </div>

        <div class="row" id="FormJenisPinjaman"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="position-relative mb-3">
                    <label for="sistem_angsuran_pokok" class="form-label">Sistem Angs. Pokok</label>
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
            <div class="col-md-4">
                <div class="position-relative mb-3">
                    <label for="sistem_angsuran_jasa" class="form-label">Sistem Angs. Jasa</label>
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
            <div class="col-md-4">
                <div class="position-relative mb-3">
                    <label for="jaminan" class="form-label">Jaminan</label>
                    <select class="js-example-basic-single form-control" name="jaminan" id="jaminan"
                        style="width: 100%;">
                        @foreach ($jaminan as $j)
                            <option value="{{ $j['id'] }}">
                                {{ $j['nama'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="msg_jaminan"></small>
                </div>
            </div>
        </div>
        <div class="row" id="formJaminan"></div>

    </form>
    <div class="col-md-12">
        <div class="font-icon-wrapper">
            <p>
            <p><b>Catatan : </b> ( Jika Ada data atau inputan yang kosong bisa di isi ( 0 ) atau ( - ) )</p>
            </p>
        </div>
    </div>
    <button type="submit" id="SimpanProposal" class="btn btn-dark custom-button">Simpan Proposal</button>
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
$(document).on('change', '#jaminan', function() {
        jaminan()
    })

    function jaminan() {
        let jaminan = $('#jaminan').val();
        $.get('/register_proposal_i/jaminan/' + jaminan, function(result) {
            $('#formJaminan').html(result.view)
        })
    }
    jaminan()

    // menentukan jenis produk pinjam
    $(document).on('change', '#jenis_produk_pinjaman', function() {
        jenis_pp()
    })

    function jenis_pp() {
        let jenis_pp = $('#jenis_produk_pinjaman').val();
        $.get('/register_proposal_i/jenis_pinjaman/' + jenis_pp, function(result) {
            $('#FormJenisPinjaman').html(result.view)
        })
    }
    jaminan()
</script>
