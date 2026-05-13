<form action="/perguliran_i/{{ $perguliran_i->id }}" method="post" id="FormInput">
    @csrf
    @method('PUT')

    <!-- INFO SUMMARY CARD -->
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-gradient-light border-bottom py-3"
            style="border-radius: 12px 12px 0 0;">
            <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-white shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px;">
                    <i class="fa fa-file-invoice text-primary font-weight-bold"></i>
                </div>
                <h6 class="mb-0 font-weight-bold text-dark">Detail Pengajuan Proposal</h6>
            </div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenProposal"
                class="btn btn-sm btn-outline-primary bg-white mb-0 shadow-sm px-3 font-weight-bold text-xs">
                <i class="fa fa-print me-2"></i> CETAK DOKUMEN
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-end-md">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 table-sm">
                            <tbody>
                                <tr>
                                    <td class="text-sm text-muted ps-0 py-2" style="width: 40%;">Tanggal Pengajuan</td>
                                    <td class="text-sm font-weight-bold text-dark py-2">:
                                        {{ Tanggal::tglIndo($perguliran_i->tgl_proposal) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-sm text-muted ps-0 py-2">Nominal Pengajuan</td>
                                    <td class="text-sm font-weight-bold text-success py-2">: Rp.
                                        {{ number_format($perguliran_i->proposal) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-sm text-muted ps-0 py-2">Jenis Jasa</td>
                                    <td class="text-sm font-weight-bold text-dark py-2">:
                                        {{ $perguliran_i->jasa->nama_jj }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6 ps-md-4">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 table-sm">
                            <tbody>
                                <tr>
                                    <td class="text-sm text-muted ps-0 py-2" style="width: 40%;">Prosentase Jasa</td>
                                    <td class="text-sm font-weight-bold text-dark py-2">:
                                        {{ $perguliran_i->pros_jasa . '% / ' . $perguliran_i->jangka . ' bulan' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-sm text-muted ps-0 py-2">Angsuran Pokok</td>
                                    <td class="text-sm font-weight-bold text-dark py-2">:
                                        {{ $perguliran_i->sis_pokok->nama_sistem }}</td>
                                </tr>
                                <tr>
                                    <td class="text-sm text-muted ps-0 py-2">Angsuran Jasa</td>
                                    <td class="text-sm font-weight-bold text-dark py-2">:
                                        {{ $perguliran_i->sis_jasa->nama_sistem }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INPUT RECOMMENDATION FORM -->
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center border-radius-md me-3 d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px;">
                    <i class="fa fa-edit text-white"></i>
                </div>
                <h6 class="mb-0 font-weight-bold text-dark">Input Rekomendasi Verifikator</h6>
            </div>
        </div>
        <div class="card-body pt-4">
            <input type="hidden" name="_id" id="_id" value="{{ $perguliran_i->id }}">
            <input type="hidden" name="status" id="status" value="V">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label for="tgl_verifikasi"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Tgl
                            Verifikasi</label>
                        <div class="input-group shadow-none">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fa fa-calendar-alt text-xs"></i></span>
                            <input autocomplete="off" type="text" name="tgl_verifikasi" id="tgl_verifikasi"
                                class="form-control date border-start-0 bg-white" value="{{ date('d/m/Y') }}">
                        </div>
                        <small class="text-danger text-xs" id="msg_tgl_verifikasi"></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label for="verifikasi"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Verifikasi
                            Rp.</label>
                        <input autocomplete="off" type="text" name="verifikasi" id="verifikasi"
                            class="form-control money font-weight-bold"
                            value="{{ number_format($perguliran_i->proposal, 2) }}">
                        <small class="text-danger text-xs" id="msg_verifikasi"></small>
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
                        <select class="selectproposal form-control" name="jenis_jasa" id="jenis_jasa">
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
                        <select class="selectproposal form-control" name="sistem_angsuran_pokok"
                            id="sistem_angsuran_pokok">
                            @foreach ($sistem_angsuran as $sa)
                                <option {{ $sa->id == $perguliran_i->sistem_angsuran ? 'selected' : '' }}
                                    value="{{ $sa->id }}">
                                    {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
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
                        <select class="selectproposal form-control" name="sistem_angsuran_jasa"
                            id="sistem_angsuran_jasa">
                            @foreach ($sistem_angsuran as $sa)
                                <option {{ $sa->id == $perguliran_i->sa_jasa ? 'selected' : '' }}
                                    value="{{ $sa->id }}">
                                    {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger text-xs" id="msg_sistem_angsuran_jasa"></small>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="form-group mb-0">
                        <label for="catatan_verifikasi"
                            class="form-control-label text-xs font-weight-bold text-uppercase text-muted">Catatan
                            Verifikasi</label>
                        <textarea class="form-control shadow-none" name="catatan_verifikasi" id="catatan_verifikasi" rows="3"
                            placeholder="Tambahkan catatan verifikasi..." style="resize:none;" spellcheck="false">{{ $perguliran_i->catatan_verifikasi }}</textarea>
                        <small class="text-danger text-xs" id="msg_catatan_verifikasi"></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS FOOTER -->
        <div class="card-footer bg-light border-top py-3 text-end">
            <button type="button" id="tdklayak"
                class="btn btn-danger btn-sm mb-0 font-weight-bold px-4 shadow-sm me-2 text-nowrap"
                @if (!in_array('perguliran.simpan_verifikator', Session::get('tombol', []))) disabled @endif>
                <i class="fa fa-ban me-2" aria-hidden="true"></i> TIDAK LAYAK
            </button>
            <button type="button" id="Simpan"
                class="btn btn-primary btn-sm mb-0 font-weight-bold px-4 shadow-sm text-nowrap"
                @if (!in_array('perguliran.simpan_verifikator', Session::get('tombol', []))) disabled @endif>
                <i class="fa fa-save me-2"></i> SIMPAN REKOM VERIFIKATOR
            </button>
        </div>
    </div>
</form>

<form action="/perguliran_i/tdklayak/{{ $perguliran_i->id }}" method="post" id="formtdklayak">
    @csrf
</form>

<script>
    $('.date').datepicker({
        dateFormat: 'dd/mm/yy'
    });
    var formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })

    $('.selectproposal').select2({
        theme: 'bootstrap4'
    });

    $(".money").maskMoney();


    $(document).on('click', '#Simpan', async function(e) {
        e.preventDefault()
        $('small').html('')

        var form = $('#FormInput')
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(result) {
                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                    window.location.href = '/detail_i/' + result.id
                })
            },
            error: function(result) {
                const respons = result.responseJSON;

                Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                $.map(respons, function(res, key) {
                    $('#' + key).parent('.input-group.input-group-static')
                        .addClass(
                            'is-invalid')
                    $('#msg_' + key).html(res)
                })
            }
        })
    })
</script>
