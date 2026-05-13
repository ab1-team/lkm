@extends('layouts.base')

@section('content')
    <div class="app-main__inner">
        <div class="tab-content">
            <div class="row">
                <div class="col-12" id="notif">

                </div>
                <div class="col-md-8 mb-3">
                    <div class="card mb-3">
                        <div class="card-body py-2">
                            <form action="/transaksi/angsuran" method="post" id="FormAngsuranKelompok">
                                @csrf

                                <input type="hidden" name="id" id="id"
                                    value="{{ Request::get('pinkel') ?: 0 }}">
                                <input type="hidden" name="_pokok" id="_pokok">
                                <input type="hidden" name="_jasa" id="_jasa">
                                <input type="hidden" name="tgl_pakai_aplikasi" id="tgl_pakai_aplikasi"
                                    value="{{ $kec->tgl_pakai }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="position-relative mb-3">
                                            <label for="tgl_transaksi">Tgl Transaksi </label>
                                            <input autocomplete="off" type="text" name="tgl_transaksi" id="tgl_transaksi"
                                                class="form-control date" value="{{ date('d/m/Y') }}">
                                            <small class="text-danger" id="msg_tgl_transaksi"></small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="position-relative mb-3">
                                            <label for="Tujuan">Tujuan</label>
                                            <select class="form-control js-example-basic-single" name="tujuan"
                                                id="tujuan">
                                                @foreach ($rekening as $rek)
                                                    <option value="{{ $rek->kode_akun }}">
                                                        {{ $rek->kode_akun }}. {{ $rek->nama_akun }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-danger" id="msg_tujuan"></small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="position-relative mb-3">
                                            <label for="pokok">Pokok </label>
                                            <input autocomplete="off" type="text" name="pokok" id="pokok"
                                                class="form-control">
                                            <small class="text-danger" id="msg_pokok"></small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="position-relative mb-3">
                                            <label for="jasa">Jasa </label>
                                            <input autocomplete="off" type="text" name="jasa" id="jasa"
                                                class="form-control">
                                            <small class="text-danger" id="msg_jasa"></small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="position-relative mb-3">
                                            <label for="denda">Denda </label>
                                            <input autocomplete="off" type="text" name="denda" id="denda"
                                                class="form-control">
                                            <small class="text-danger" id="msg_denda"></small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="position-relative mb-3">
                                            <label for="total">Total Bayar </label>
                                            <input autocomplete="off" readonly disabled type="text" name="total"
                                                id="total" class="form-control">
                                            <small class="text-danger" id="msg_total"></small>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="d-flex justify-content-end">
                                <button type="button"class="btn btn-warning btn-sm me-3" style="color: white;">
                                    Loan id
                                    <span class="badge badge-info" id="loan-id" style="font-size: 16px;">
                                    </span>
                                </button>
                                <button type="button" id="btnAngsuranAnggota" class="btn btn-info btn-sm me-3">
                                    Detail Kelompok
                                </button>
                                <button type="button" id="SimpanAngsuran"
                                    class="btn btn-github btn-sm btn btn-sm btn-dark mb-0">Posting</button>
                            </div>
                        </div>
                    </div>

                    <div class="card card-body p-2 pb-0 mb-3">
                        <div class="row">
                            <div class="col-4">
                                <div class="d-grid">
                                    <a id="cetakKartuAngsuran" class="btn btn-success btn-sm mb-2">Kartu</a>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-grid">
                                    <button class="btn btn-danger btn-sm mb-2" id="btnDetailAngsuran">Riwayat Angsuran</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-grid">
                                    <button class="btn btn-info btn-sm mb-2" id=cetakLPP>LPP per bulan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="nav-wrapper position-relative end-0">
                        <div class="card card-body p-1 mb-3">
                            <div class="d-flex justify-content-between" role="tablist">
                                <button class="btn btn-outline-warning flex-fill me-1 mb-0 active" data-bs-toggle="tab"
                                    data-bs-target="#Pokok" role="tab" aria-controls="Pokok" aria-selected="true">
                                    Pokok
                                </button>
                                <button class="btn btn-outline-warning flex-fill mb-0" data-bs-toggle="tab" data-bs-target="#Jasa"
                                    role="tab" aria-controls="Jasa" aria-selected="false">
                                    Jasa
                                </button>
                            </div>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="Pokok" role="tabpanel"
                                aria-labelledby="Pokok">
                                <div class="card card-body p-2">
                                    <canvas id="chartP"></canvas>
                                    <div class="d-flex justify-content-between mt-3 mb-1 mx-3 text-sm fw-bold">
                                        <span>Alokasi</span>
                                        <span id="alokasi_pokok"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="Jasa" role="tabpanel" aria-labelledby="Jasa">
                                <div class="card card-body p-2">
                                    <canvas id="chartJ"></canvas>
                                    <div class="d-flex justify-content-between mt-3 mb-1 mx-3 text-sm fw-bold">
                                        <span>Jasa</span>
                                        <span id="alokasi_jasa"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal fade" id="DetailAngsuran" tabindex="-1" aria-labelledby="DetailAngsuranLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="DetailAngsuranLabel">

                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutDetailAngsuran"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="BuktiAngsuran" tabindex="-1" aria-labelledby="BuktiAngsuranLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="BuktiAngsuranLabel">

                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutBuktiAngsuran"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="BtnCetakBkm" class="btn btn-info btn-sm">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="tutupBuktiAngsuran">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="AngsuranAnggota" tabindex="-1" aria-labelledby="AngsuranAnggotaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="AngsuranAnggotaLabel">
                        Detail Kelompok
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="LayoutAngsuranAnggota"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <form action="/transaksi/hapus_angsuran_kelompok" method="post" id="formHapus">
        @csrf

        <input type="hidden" name="del_idt" id="del_idt">
        <input type="hidden" name="del_idtp" id="del_idtp">
        <input type="hidden" name="del_id_pinj" id="del_id_pinj">
    </form>
@endsection

@section('script')
    <script>
        var pinkel = '{{ Request::get('pinkel') ?: 0 }}'

        if (pinkel != 0) {
            $('#id').val(pinkel)
        }

        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })

        $("#pokok").maskMoney({
            allowNegative: true
        });
        $("#jasa").maskMoney({
            allowNegative: true
        });
        $("#denda").maskMoney({
            allowNegative: true
        });

        $(document).ready(function() {
            $(".date").flatpickr({
                dateFormat: "d/m/Y"
            })

            $('.js-example-basic-single').select2({
                theme: 'bootstrap4'
            });


        });

        var chartP;
        var chartJ;

        $(document).on('change', '#id', function(e) {
            var id = $(this).val()
            if (id == '' || id == '0') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Silahkan pilih Kelompok terlebih dahulu!',
                })
            } else {
                $.get('/transaksi/detail/' + id, function(result) {
                    if (result.status == 'success') {
                        var alokasi_pokok = result.alokasi_pokok ?? 0
                        var alokasi_jasa  = result.alokasi_jasa  ?? 0
                        var real_pokok    = result.real_pokok    ?? 0
                        var real_jasa     = result.real_jasa     ?? 0

                        $('#loan-id').html(result.perguliran.id)
                        $('#_pokok').val(alokasi_pokok)
                        $('#_jasa').val(alokasi_jasa)

                        $('#alokasi_pokok').html('Rp. ' + formatter.format(alokasi_pokok))
                        $('#alokasi_jasa').html('Rp. ' + formatter.format(alokasi_jasa))

                        if (typeof chartP != "undefined") {
                            chartP.destroy();
                        }
                        if (typeof chartJ != "undefined") {
                            chartJ.destroy();
                        }

                        var ctxP = document.getElementById('chartP').getContext('2d');
                        chartP = new Chart(ctxP, {
                            type: 'doughnut',
                            data: {
                                labels: ['Pokok', 'Sisa'],
                                datasets: [{
                                    label: 'Rp. ',
                                    data: [real_pokok, alokasi_pokok - real_pokok],
                                    backgroundColor: [
                                        'rgb(54, 162, 235)',
                                        'rgb(255, 99, 132)',
                                    ],
                                    hoverOffset: 4
                                }]
                            },
                        });

                        var ctxJ = document.getElementById('chartJ').getContext('2d');
                        chartJ = new Chart(ctxJ, {
                            type: 'doughnut',
                            data: {
                                labels: ['Jasa', 'Sisa'],
                                datasets: [{
                                    label: 'Rp. ',
                                    data: [real_jasa, alokasi_jasa - real_jasa],
                                    backgroundColor: [
                                        'rgb(255, 205, 86)',
                                        'rgb(255, 99, 132)',
                                    ],
                                    hoverOffset: 4
                                }]
                            },
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: result.msg,
                        })
                    }
                })
            }
        })

        $(document).on('change', '#pokok,#jasa,#denda', function(e) {
            var pokok = $('#pokok').val()
            var jasa = $('#jasa').val()
            var denda = $('#denda').val()

            pokok = parseFloat(pokok.split(',').join('').split('.00').join(''))
            if (!pokok) {
                pokok = 0;
                $('#pokok').val(formatter.format('0'))
            }

            jasa = parseFloat(jasa.split(',').join('').split('.00').join(''))
            if (!jasa) {
                jasa = 0;
                $('#jasa').val(formatter.format('0'))
            }

            denda = parseFloat(denda.split(',').join('').split('.00').join(''))
            if (!denda) {
                $('#denda').val(formatter.format('0'))
                denda = 0;
            }

            var total = pokok + jasa + denda
            $('#total').val(formatter.format(total))
        })

        $(document).on('click', '#SimpanAngsuran', function(e) {
            e.preventDefault()
            $('small').html('')

            var form = $('#FormAngsuranKelompok')

            var loading = Swal.fire({
                title: "Mohon Menunggu..",
                html: "Memproses transaksi angsuran.",
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })

            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    loading.close()
                    
                    if (result.success) {
                        $.get('/angsuran/notifikasi/' + result.idtp, function(res) {
                            $('#notif').html(res.view)
                        })

                        Swal.fire('Berhasil!', result.msg, 'success').then(() => {
                            $('#pokok').val('0.00')
                            $('#jasa').val('0.00')
                            $('#denda').val('0.00')
                            $('#total').val('0.00')
                            
                            $('#id').trigger('change')
                        })

                        if (result.whatsapp) {
                            sendMsg(result.number, result.nama_kelompok, result.pesan)
                        }
                    } else {
                        loading.close()
                        Swal.fire('Error', result.msg, 'warning')
                    }
                },
                error: function(result) {
                    loading.close()
                    Swal.fire('Error', '', 'warning')
                    
                    if (result.responseJSON && result.responseJSON.errors) {
                        $.each(result.responseJSON.errors, function(index, val) {
                            $('#msg_' + index).html(val[0])
                        })
                    }
                }
            })
        })

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault()

            var idt = $(this).attr('data-idt')
            $.get('/transaksi/data/' + idt, function(result) {

                $('#del_idt').val(result.idt)
                $('#del_idtp').val(result.idtp)
                $('#del_id_pinj').val(result.id_pinj)
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Setelah menekan tombol Hapus Transaksi dibawah, maka transaksi ini akan dihapus dari aplikasi secara permanen.',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus Transaksi',
                    cancelButtonText: 'Batal',
                    icon: 'warning'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = $('#formHapus')
                        $.ajax({
                            type: form.attr('method'),
                            url: form.attr('action'),
                            data: form.serialize(),
                            success: function(result) {
                                if (result.success) {
                                    Swal.fire('Berhasil!', result.msg, 'success')
                                        .then(() => {
                                            $('#DetailAngsuran').modal('hide')
                                            $('#id').trigger('change')
                                        })
                                }
                            }
                        })
                    }
                })
            })
        })

        $(document).on('click', '#cetakKartuAngsuran', function(e) {
            e.preventDefault()
            var id_pinj = $('#id').val()

            open_window('/perguliran/dokumen/kartu_angsuran/' + id_pinj)
        })

        $(document).on('click', '#cetakLPP', function(e) {
            e.preventDefault()
            var id_pinj = $('#id').val()

            open_window('/transaksi/angsuran/lpp/' + id_pinj)
        })

        $(document).on('click', '#btnDetailAngsuran', function(e) {
            var id = $('#id').val()

            $.get('/transaksi/angsuran/detail_angsuran/' + id, function(result) {
                $('#DetailAngsuran').modal('show')

                $('#DetailAngsuranLabel').html(result.label)
                $('#LayoutDetailAngsuran').html(result.view)

                $('#BuktiAngsuranLabel').html(result.label_cetak)
                $('#LayoutBuktiAngsuran').html(result.cetak)
            })
        })

        $(document).on('click', '#cetakBuktiAngsuran, #tutupBuktiAngsuran', function(e) {
            e.preventDefault()

            $('#BuktiAngsuran').modal('toggle');
        })

        $(document).on('click', '#BtnCetakBkm', function(e) {
            e.preventDefault()

            $('#FormCetakBuktiAngsuran').attr('action', '/transaksi/angsuran/cetak_bkm');
            $('#FormCetakBuktiAngsuran').submit();
        })

        $(document).on('click', '#btnAngsuranAnggota', function(e) {
            e.preventDefault()
            
            var id = $('#id').val()
            if (id == '' || id == '0') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Silahkan pilih Kelompok terlebih dahulu!',
                })
                return false
            }

            $.get('/transaksi/angsuran/angsuran_anggota/' + id, function(result) {
                $('#AngsuranAnggota').modal('show')
                $('#LayoutAngsuranAnggota').html(result.view)
            })
        })

        $(document).on('click', '.btn-link', function(e) {
            var action = $(this).attr('data-action')

            open_window(action)
        })

        $(document).on('click', '.btn-struk', function(e) {
            e.preventDefault()

            var idtp = $(this).attr('data-idtp')
            Swal.fire({
                title: "Cetak Kuitansi Angsuran",
                showDenyButton: true,
                confirmButtonText: "Biasa",
                denyButtonText: "Dot Matrix",
                confirmButtonColor: "#3085d6",
                denyButtonColor: "#3085d6",
            }).then((result) => {
                if (result.isConfirmed) {
                    open_window('/transaksi/angsuran/struk/' + idtp)
                } else if (result.isDenied) {
                    open_window('/transaksi/angsuran/struk_matrix/' + idtp)
                }
            });
        })

        function sendMsg(number, nama, msg) {
            $.ajax({
                type: 'post',
                url: '{{ $api }}/api/send/text',
                timeout: 0,
                headers: {
                    "Content-Type": "application/json",
                    "x-api-key": "{{ $api_key }}"
                },
                data: JSON.stringify({
                    device_id: "{{ $wa_device_id }}",
                    to: number,
                    message: msg
                }),
                success: function(result) {
                    if (result.status || result.success) {
                        MultiToast('success', 'Pesan untuk Nasabah ' + nama + ' berhasil dikirim')
                    } else {
                        MultiToast('error', 'Pesan untuk Nasabah ' + nama + ' gagal dikirim')
                    }
                },
                error: function(result) {
                    MultiToast('error', 'Pesan untuk Nasabah ' + nama + ' gagal dikirim')
                }
            })
        }

        if (pinkel != 0) {
            $('#id').trigger('change')
        }
    </script>
@endsection
