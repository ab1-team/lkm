@extends('layouts.base')
@php
    $tahun = $tahun ?? date('Y');
@endphp
@section('content')
    <div class="app-main__inner">
        <div class="tab-content">
            <div class="card card-body py-2 mb-3">
                @if ($success)
                    <div class="alert alert-success alert-dismissible text-bl fade show" role="alert">
                        <span class="alert-icon align-middle">
                            &nbsp;
                        </span>
                        <span class="alert-text">
                            <i class="fa-brands fa-signal-messenger"></i>
                            <strong>Tutup Buku Tahun {{ $tahun }}</strong> berhasil.
                        </span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <form action="" method="post" id="FormTahunTutupBuku">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="position-relative mb-3"><label for="exampleEmail11"
                                    class="form-label">Tahun</label>
                                <select class="tutupbukuselect2 form-select" name="tahun" id="tahun" style="width: 100%;">
                                    @php
                                        $tgl_pakai = $kec->tgl_pakai;
                                        $th_pakai = explode('-', $tgl_pakai)[0];
                                    @endphp
                                    @for ($i = date('Y'); $i >= $th_pakai; $i--)
                                        <option {{ $i == date('Y') ? 'selected' : '' }} value="{{ $i }}">
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <small class="text-danger" id="msg_tahun"></small>

                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="pembagian_laba" id="pembagian_laba" value="true">

                </form>
                <div class="d-flex justify-content-end"style=" float: right;">
                    <button type="button" id="TutupBuku" {{ date('m') <= 10 ? 'disabled' : '' }}
                        class="btn btn-sm btn-dark mb-0 ms-3 ">1. Tutup Buku</button>
                    <button type="button" id="PembagianLaba" class="btn btn-sm btn-success mb-0 ms-3">
                        2. Simpan Alokasi Laba
                    </button>
                </div>
            </div>
        </div>
        <div id="LayoutPreview">

            <div class="tab-content">
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="p-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.tutupbukuselect2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });
        var tahun = "{{ date('Y') }}"
        var bulan = "{{ date('m') }}"

        $(document).on('change', 'select#tahun', function(e) {
            e.preventDefault()

            var tahun_val = $(this).val()
            if ((tahun == tahun_val && bulan <= 10)) {
                $('#TutupBuku').prop("disabled", true)
            } else {
                $('#TutupBuku').prop("disabled", false)
            }
        })

        $(document).on('click', '#TutupBuku', function(e) {
            e.preventDefault()
            $('#FormTahunTutupBuku').attr('action', '/transaksi/tutup_buku/saldo')
            $('#LayoutPreview').html(
                '<div class="card"><div class="card-body p-3"><div class="p-2"></div></div></div>')

            var form = $('#FormTahunTutupBuku')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        $('#LayoutPreview').html(result.view)
                    }
                }
            })
        })

        let childWindow;
        $(document).on('click', '#SimpanSaldo', function(e) {
            e.preventDefault()

            childWindow = window.open('/simpan_saldo?bulan=00&tahun=' + tahun, '_blank');
        })

        window.addEventListener('message', function(event) {
            if (event.data === 'closed') {
                $('#FormTahunTutupBuku').attr('action', '/transaksi/tutup_buku/saldo')
                $('#LayoutPreview').html(
                    '<div class="card"><div class="card-body p-3"><div class="p-2"></div></div></div>')

                var form = $('#FormTahunTutupBuku')
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success) {
                            $('#LayoutPreview').html(result.view)
                        }
                    }
                })
            }
        })

        $(document).on('click', '#PembagianLaba', function(e) {
            e.preventDefault()
            $('#FormTahunTutupBuku').attr('action', '/transaksi/tutup_buku')

            $('#FormTahunTutupBuku').submit()
        })
    </script>
@endsection
