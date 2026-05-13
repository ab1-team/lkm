<!-- DUAL SUMMARY CARDS -->
<div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
    <div class="card-header d-flex justify-content-between align-items-center bg-gradient-light border-bottom py-3"
        style="border-radius: 12px 12px 0 0;">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-white shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center"
                style="width: 36px; height: 36px;">
                <i class="fa fa-check-circle text-info font-weight-bold"></i>
            </div>
            <h6 class="mb-0 font-weight-bold text-dark">Rangkuman Hasil Verifikasi</h6>
        </div>
        <div class="d-flex gap-2">
            <form action="/perguliran_i/dokumen?status=P" target="_blank" method="post" class="m-0">
                @csrf
                <input type="hidden" name="id" value="{{ $perguliran_i->id }}">
                <button type="submit" name="report" value="RekomendasiVerifikator#pdf"
                    class="btn btn-sm btn-outline-secondary bg-white shadow-sm mb-0 px-3 font-weight-bold text-xs">
                    <i class="fa fa-file-pdf me-2"></i> CETAK REKOM
                </button>
            </form>
            <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenProposal"
                class="btn btn-sm btn-outline-info bg-white mb-0 shadow-sm px-3 font-weight-bold text-xs">
                <i class="fa fa-print me-2"></i> CETAK PROPOSAL
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- COL 1: ORIGINAL PROPOSAL -->
            <div class="col-md-6 border-end-md">
                <span class="badge bg-light text-dark text-xxs mb-2 font-weight-bolder px-2">STATUS: PROPOSAL</span>
                <div class="table-responsive">
                    <table class="table align-items-center mb-0 table-sm">
                        <tbody>
                            <tr>
                                <td class="text-sm text-muted ps-0 py-1" style="width: 40%;">Nominal Diajukan</td>
                                <td class="text-sm font-weight-bold text-dark py-1">: Rp.
                                    {{ number_format($perguliran_i->proposal) }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-muted ps-0 py-1">Prosentase Jasa</td>
                                <td class="text-sm font-weight-bold text-dark py-1">:
                                    {{ $perguliran_i->pros_jasa . '% / ' . $perguliran_i->jangka . ' bln' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-muted ps-0 py-1">Angsuran Pokok</td>
                                <td class="text-sm font-weight-bold text-dark py-1">:
                                    {{ $perguliran_i->sis_pokok->nama_sistem }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- COL 2: VERIFIED STATUS -->
            <div class="col-md-6 ps-md-4">
                <span class="badge bg-info text-white text-xxs mb-2 font-weight-bolder px-2">STATUS: VERIFIED</span>
                <div class="table-responsive">
                    <table class="table align-items-center mb-0 table-sm">
                        <tbody>
                            <tr>
                                <td class="text-sm text-muted ps-0 py-1" style="width: 40%;">Nominal Disetujui</td>
                                <td class="text-sm font-weight-bold text-info py-1">: Rp.
                                    {{ number_format($perguliran_i->verifikasi) }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-muted ps-0 py-1">Tgl Verifikasi</td>
                                <td class="text-sm font-weight-bold text-dark py-1">:
                                    {{ Tanggal::tglIndo($perguliran_i->tgl_verifikasi) }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-muted ps-0 py-1">Angsuran Jasa</td>
                                <td class="text-sm font-weight-bold text-dark py-1">:
                                    {{ $perguliran_i->sis_jasa->nama_sistem }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INPUT DECISION FORM -->
<form action="/perguliran_i/{{ $perguliran_i->id }}" method="post" id="FormInput">
    @csrf
    @method('PUT')

    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-gradient-success shadow-success text-center border-radius-md me-3 d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px;">
                    <i class="fa fa-gavel text-white"></i>
                </div>
                <h6 class="mb-0 font-weight-bold text-dark">Form Keputusan Pendanaan</h6>
            </div>
        </div>
        <div class="card-body pt-4">
            <input type="hidden" name="_id" id="_id" value="{{ $perguliran_i->id }}">
            <input type="hidden" name="status" id="status" value="W">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label for="tgl_tunggu"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Tgl
                            Tunggu</label>
                        <div class="input-group shadow-none">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fa fa-calendar-alt text-xs"></i></span>
                            <input autocomplete="off" type="text" name="tgl_tunggu" id="tgl_tunggu"
                                class="form-control date border-start-0 bg-white" value="{{ date('d/m/Y') }}">
                        </div>
                        <small class="text-danger text-xs" id="msg_tgl_tunggu"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label for="harga"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Harga
                            Pendanaan (Rp)</label>
                        <input autocomplete="off" type="text" name="harga" id="harga"
                            class="form-control money font-weight-bold"
                            value="{{ number_format($perguliran_i->verifikasi, 2) }}">
                        <small class="text-danger text-xs" id="msg_harga"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label for="jangka"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Jangka
                            (Bulan)</label>
                        <input autocomplete="off" type="number" name="jangka" id="jangka" class="form-control"
                            value="{{ $perguliran_i->jangka }}">
                        <small class="text-danger text-xs" id="msg_jangka"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label for="pros_jasa"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Prosentase
                            Jasa (%)</label>
                        <input autocomplete="off" type="number" name="pros_jasa" id="pros_jasa"
                            class="form-control" value="{{ $perguliran_i->pros_jasa }}">
                        <small class="text-danger text-xs" id="msg_pros_jasa"></small>
                    </div>
                </div>
            </div>

            <hr class="horizontal dark my-3">

            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="jenis_jasa"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Jenis
                            Jasa</label>
                        <select class="selectverived form-control" name="jenis_jasa" id="jenis_jasa">
                            @foreach ($jenis_jasa as $jj)
                                <option {{ $jj->id == $perguliran_i->jenis_jasa ? 'selected' : '' }}
                                    value="{{ $jj->id }}">
                                    {{ $jj->nama_jj }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger text-xs" id="msg_jenis_jasa"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="sistem_angsuran_pokok"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Sistem
                            Angsuran Pokok</label>
                        <select class="selectverived form-control" name="sistem_angsuran_pokok"
                            id="sistem_angsuran_pokok">
                            @foreach ($sistem_angsuran as $sa)
                                <option {{ $sa->id == $perguliran_i->sistem_angsuran ? 'selected' : '' }}
                                    value="{{ $sa->id }}">
                                    {{ $sa->nama_sistem }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger text-xs" id="msg_sistem_angsuran_pokok"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="sistem_angsuran_jasa"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Sistem
                            Angsuran Jasa</label>
                        <select class="selectverived form-control" name="sistem_angsuran_jasa"
                            id="sistem_angsuran_jasa">
                            @foreach ($sistem_angsuran as $sa)
                                <option {{ $sa->id == $perguliran_i->sa_jasa ? 'selected' : '' }}
                                    value="{{ $sa->id }}">
                                    {{ $sa->nama_sistem }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger text-xs" id="msg_sistem_angsuran_jasa"></small>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="tgl_cair"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Tgl
                            Pencairan</label>
                        <div class="input-group shadow-none">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fa fa-money-bill-wave text-xs"></i></span>
                            <input autocomplete="off" type="text" name="tgl_cair" id="tgl_cair"
                                class="form-control date border-start-0 bg-white" value="{{ date('d/m/Y') }}">
                        </div>
                        <small class="text-danger text-xs" id="msg_tgl_cair"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="depe"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Down Payment
                            (%)</label>
                        <input autocomplete="off" type="text" name="depe" id="depe"
                            class="form-control money"
                            value="{{ number_format(($perguliran_i->verifikasi * $kec->def_depe) / 100, 2) }}">
                        <small class="text-danger text-xs" id="msg_depe"></small>
                    </div>
                </div>
                <!-- HIDE SPK OPTION UNLESS REVEALED BY ORIGINAL FLOW -->
                <div class="col-md-4 d-none">
                    <div class="form-group mb-3">
                        <label for="nomor_spk"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Nomor
                            SPK</label>
                        <input autocomplete="off" type="text" name="nomor_spk" id="nomor_spk"
                            class="form-control" value="{{ $perguliran_i->spk_no }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div
            class="card-footer bg-light border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <button type="button" id="kembaliProposal"
                class="btn btn-outline-secondary btn-sm mb-0 font-weight-bold shadow-sm text-nowrap"
                @if (!in_array('perguliran.balik_proposal', Session::get('tombol', []))) disabled @endif>
                <i class="fa fa-arrow-left me-2"></i> KEMBALI KE PROPOSAL
            </button>
            <div class="text-end">
                <button type="button" id="tdklayak"
                    class="btn btn-danger btn-sm mb-0 font-weight-bold px-4 shadow-sm text-nowrap me-2"
                    @if (!in_array('perguliran.simpan_verifikator', Session::get('tombol', []))) disabled @endif>
                    <i class="fa fa-ban me-2"></i> TIDAK LAYAK
                </button>
                <button type="button" id="Simpan"
                    class="btn btn-success btn-sm mb-0 font-weight-bold px-4 shadow-sm text-nowrap"
                    @if (!in_array('perguliran.simpan_dana', Session::get('tombol', []))) disabled @endif>
                    <i class="fa fa-save me-2"></i> SIMPAN KEPUTUSAN
                </button>
            </div>
        </div>
    </div>
</form>

<form action="/perguliran_i/kembali_proposal/{{ $perguliran_i->id }}" method="post" id="formKembaliProposal">
    @csrf
</form>
<form action="/perguliran_i/tdklayak/{{ $perguliran_i->id }}" method="post" id="formtdklayak">
    @csrf
</form>

<script>
    $('.date').datepicker({
        dateFormat: 'dd/mm/yy'
    });

    $('.selectverived').select2({
        theme: 'bootstrap-5'
    });

    $(".money").maskMoney();

    $(document).off('click', '#Simpan').on('click', '#Simpan', async function(e) {
        e.preventDefault();
        $('small').html('');

        var form = $('#FormInput');
        $.ajax({
            type: 'POST',
            url: form.attr('action') + '?save=true',
            data: form.serialize(),
            success: function(result) {
                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                    window.location.href = '/detail_i/' + result.id;
                });
            },
            error: function(result) {
                const respons = result.responseJSON;
                Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');

                $.map(respons, function(res, key) {
                    $('#msg_' + key).html(res);
                });
            }
        });
    });
</script>
