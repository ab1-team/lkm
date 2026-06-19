@extends('layouts.base')

@section('content')
<style>
    .tox .tox-promotion {
    background: repeating-linear-gradient(transparent 0 1px, transparent 1px 39px) center top 39px / 100% calc(100% - 39px) no-repeat;
    background-color: #fff;
    grid-column: 2;
    grid-row: 1;
    padding-inline-end: 8px;
    padding-inline-start: 4px;
    padding-top: 5px;
    display: none;
    }
    .tox .tox-statusbar__branding svg {
    fill: rgba(34, 47, 62, .8);
    height: 1.14em;
    vertical-align: -.28em;
    width: 3.6em;
    display: none;
}
</style>
    <div class="app-main__inner">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title">Pengaturan Tanda Tangan <b>Dokumen</b></h5>

                                <div class="form-group row align-items-center mb-3">
                                    <label for="jenis_dokumen" class="col-sm-2 col-form-label">Jenis Dokumen</label>
                                    <div class="col-sm-6">
                                        <select id="jenis_dokumen" class="form-select form-select-sm" data-placeholder="Pilih dokumen" {{ empty($daftarJenis) ? 'disabled' : '' }}>
                                            @if (!empty($statis))
                                                <optgroup label="Standar">
                                                    @foreach ($statis as $key => $label)
                                                        <option value="{{ $key }}" {{ $key == $jenis ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            @if (!empty($pinjaman))
                                                <optgroup label="Dokumen Pinjaman">
                                                    @foreach ($pinjaman as $key => $label)
                                                        <option value="{{ $key }}" {{ $key == $jenis ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <form action="/pengaturan/sop/simpanttdpelaporan" method="post" id="formTtdDokumen">
                                    @csrf

                                    <input type="hidden" name="field" id="field" value="tanda_tangan_{{ $jenis }}">
                                    <input type="hidden" name="jenis" id="jenis_input" value="{{ $jenis }}">
                                    <textarea class="tiny-mce-editor" name="tanda_tangan" id="tanda_tangan" rows="20">
                                    @if ($ttd)
                                    {!! json_decode($ttd->tanda_tangan, true) !!}
                                    @elseif ($jenis == 'laporan')
                                    @else
                                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                                        <tr>
                                            <td align="center">Pihak Pertama</td>
                                            <td>&nbsp;</td>
                                            <td align="center">Pihak Kedua</td>
                                        </tr>
                                            <tr>
                                            <td></td>
                                        </tr>
                                    </table>
                                    @endif
                                    </textarea>
                                </form>

                                <small id="tanggal-hint" class="text-danger" style="display: none;">
                                    Masukkan <span style="text-transform: lowercase">*{tanggal}*</span> pada form tanda tangan untuk menuliskan tanggal laporan dibuat. <b>Hapus tanda bintang (*)</b>
                                </small>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" id="resetTtdDokumen" class="btn btn-danger btn-sm">
                                        Reset
                                    </button>
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#kataKunci" class="btn btn-info btn-sm ms-2">
                                        Kata Kunci
                                    </button>
                                    <button type="button" id="simpanTtdDokumen" class="btn btn-dark btn-sm ms-2">
                                        Simpan Perubahan
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <br><br><br><br>
@endsection

@section('modal')
    <div class="modal fade" id="kataKunci" tabindex="-1" aria-labelledby="kataKunciLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="kataKunciLabel">Kata Kunci</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped midle">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th width="10">No</th>
                                <th width="100">Kata Kunci</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($keyword as $k)
                                <tr>
                                    <td align="center">{{ $loop->iteration }}</td>
                                    <td>{{ $k['key'] }}</td>
                                    <td>{{ $k['des'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const TTD_DEFAULT_TTD = `<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;"><tr><td align="center">Pihak Pertama</td><td>&nbsp;</td><td align="center">Pihak Kedua</td></tr><tr><td></td></tr></table>`;

        function applyTanggalHint(show) {
            $('#tanggal-hint').toggle(show);
        }

        function setTinymceContent(html) {
            if (typeof tinymce === 'undefined') return;
            const ed = tinymce.get('tanda_tangan');
            if (ed) {
                ed.setContent(html || '');
            } else {
                $('#tanda_tangan').val(html || '');
            }
        }

        $(function() {
            $('#jenis_dokumen').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih dokumen',
                allowClear: false,
            });

            $('#jenis_dokumen').on('change', function() {
                const jenis = $(this).val();
                console.log('[TTD] change fired, jenis=', JSON.stringify(jenis));
                if (!jenis) {
                    Toastr('error', 'Jenis dokumen kosong / tidak valid');
                    return;
                }
                $.ajax({
                    url: '/ttd-dokumen/data',
                    data: { jenis: jenis },
                    success: function(res) {
                        console.log('[TTD] response', res);
                        if (!res.success) {
                            Toastr('error', res.msg || 'Gagal memuat data');
                            return;
                        }
                        $('#field').val('tanda_tangan_' + res.jenis);
                        $('#jenis_input').val(res.jenis);
                        setTinymceContent(res.tanda_tangan || (res.jenis === 'laporan' ? '' : TTD_DEFAULT_TTD));
                        applyTanggalHint(!!res.tanggal);
                    },
                    error: function(xhr) {
                        console.log('[TTD] ajax error', xhr.status, xhr.responseText);
                        Toastr('error', 'Gagal memuat tanda tangan');
                    }
                });
            });

            $(document).on('click', '#simpanTtdDokumen', function(e) {
                e.preventDefault()

                tinymce.triggerSave()
                var form = $('#formTtdDokumen')
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success) {
                            Toastr('success', result.msg)
                        }
                    }
                })
            })

            $(document).on('click', '#resetTtdDokumen', function(e) {
                e.preventDefault()
                const jenis = $('#jenis_dokumen').val()
                if (!jenis) {
                    Toastr('error', 'Pilih jenis dokumen terlebih dahulu')
                    return
                }
                if (!confirm('Reset tanda tangan untuk dokumen ini? Data tanda tangan akan dihapus dan kembali ke default.')) {
                    return
                }
                $.ajax({
                    type: 'DELETE',
                    url: '/ttd-dokumen',
                    data: { jenis: jenis, _token: $('input[name="_token"]').first().val() },
                    success: function(result) {
                        if (result.success) {
                            Toastr('success', result.msg)
                            setTinymceContent(jenis === 'laporan' ? '' : TTD_DEFAULT_TTD)
                            applyTanggalHint(false)
                        } else {
                            Toastr('error', result.msg || 'Gagal reset')
                        }
                    },
                    error: function() {
                        Toastr('error', 'Gagal reset tanda tangan')
                    }
                })
            })

            applyTanggalHint(@json($tanggal));
        });
    </script>
@endsection
