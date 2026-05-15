@extends('layouts.base')

@section('content')
    @php
        $hasSimpananMenu = false;
        $parent_menu = session('menu') ?? collect([]);
        foreach ($parent_menu as $item) {
            if (stripos($item->title, 'Simpanan') !== false || stripos($item->link, 'simpanan') !== false) {
                $hasSimpananMenu = true;
                break;
            }
            if (isset($item->child)) {
                foreach ($item->child as $child) {
                    if (stripos($child->title, 'Simpanan') !== false || stripos($child->link, 'simpanan') !== false) {
                        $hasSimpananMenu = true;
                        break 2;
                    }
                    if (isset($child->child)) {
                        foreach ($child->child as $subchild) {
                            if (stripos($subchild->title, 'Simpanan') !== false || stripos($subchild->link, 'simpanan') !== false) {
                                $hasSimpananMenu = true;
                                break 3;
                            }
                        }
                    }
                }
            }
        }
    @endphp
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/css/lkm-sop.css">

    <div class="app-main__inner">

        <div class="tab-content">
            <div class="tab-pane fade show active" id="" role="tabpanel">
                <div class="row">
                    <div class="col-md-3 settings-sidebar-col">
                        <div class="main-card mb-3 card settings-sidebar-card">
                            <div class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav flex-column settings-nav-list">
                                <div class="settings-sidebar-title">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>Pengaturan SOP</span>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item active" id="wellcome" data-bs-toggle="tab" href="#tab-content-0">
                                        <i class="fa-solid fa-home"></i>
                                        <span>Wellcome</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="lembaga" data-bs-toggle="tab" href="#tab-content-1">
                                        <i class="fa-solid fa-tree-city"></i>
                                        <span>Identitas Lembaga</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="pengelola" data-bs-toggle="tab" href="#tab-content-2">
                                        <i class="fa-solid fa-person-chalkboard"></i>
                                        <span>Sebutan Pengelola</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="peminjam" data-bs-toggle="tab" href="#tab-content-3">
                                        <i class="fa-solid fa-chart-simple"></i>
                                        <span>Sistem Pinjaman</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-9">
                                        <i class="fa-solid fa-square-poll-horizontal"></i>
                                        <span>Kolektibilitas</span>
                                    </a>
                                </div>
                                @if ($hasSimpananMenu)
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="simpanan" data-bs-toggle="tab" href="#tab-content-8">
                                        <i class="fa-solid fa-vault"></i>
                                        <span>Sistem Simpanan</span>
                                    </a>
                                </div>
                                @endif
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="asuransi" data-bs-toggle="tab" href="#tab-content-4">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                        <span>Pengaturan Asuransi</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-5">
                                        <i class="fa-solid fa-laptop-file"></i>
                                        <span>Redaksi SPK</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-kustomisasi-calk">
                                        <i class="fa-solid fa-laptop-file"></i>
                                        <span>Kustomisasi CALK</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-6">
                                        <i class="fa-solid fa-panorama"></i>
                                        <span>Logo</span>
                                    </a>
                                </div>
                                <div class="mb-1">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-7">
                                        <i class="fa-solid fa-camera-rotate"></i>
                                        <span>Whatsapp</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 settings-content-col">
                        <div class="tab-content">
                            <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                                @include('sop.partials._wellcome')
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-1" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Identitas Lembaga</h5>
                                            @include('sop.partials._lembaga')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-2" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Sebutan Pengelola Lembaga</h5>
                                            <div class="position-relative mb-3">
                                                @include('sop.partials._pengelola')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-3" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Sistem Peminjam</h5>
                                            @include('sop.partials._pinjaman')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-4" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Pengaturan Asuransi</h5>
                                            @include('sop.partials._asuransi')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-5" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Redaksi Dokumen SPK</h5>
                                            @include('sop.partials._spk')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-9" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Tingkatan Kolektibilitas Pinjaman</h5>
                                            @include('sop.partials._kolek')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-kustomisasi-calk" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Kustomisasi SPK</h5>
                                            @include('sop.partials._kustomisasi_calk')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-6" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Upload LOGO</h5>
                                            @include('sop.partials._logo')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-7" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Pengaturan Whatsapp</h5>
                                            @include('sop.partials._whatsapp')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($hasSimpananMenu)
                            <div class="tab-pane tabs-animation fade" id="tab-content-8" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Pengaturan Simpanan</h5>
                                            @include('sop.partials._simpanan')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <form action="/pengaturan/whatsapp/{{ $token }}" method="post" id="FormWhatsapp">
        @csrf
    </form>
@endsection

@section('script')
    <script src="/vendor/ckeditor/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.min.js"></script>

    <script>
        let ListContainer = $('#Pesan')
        const API = '{{ $api }}'
        const MASTER_KEY = '{{ $api_key }}'
        const SAVED_ID = '{{ $device_id }}'
        const SAVED_KEY = '{{ $device_key }}'

        const CURRENT_ID = SAVED_ID || '{{ $token }}'
        const CURRENT_KEY = SAVED_KEY || MASTER_KEY

        var socket;

        function saveLocalSession(id, key) {
            $.ajax({
                type: 'POST',
                url: '/pengaturan/whatsapp/save_device',
                data: {
                    _token: '{{ csrf_token() }}',
                    device_id: id,
                    device_key: key
                },
                success: function(res) {
                    console.log('Session saved to DB:', res);
                }
            })
        }

        function initSocket(id, key) {
            if (socket) {
                socket.disconnect();
            }

            socket = io(API, {
                query: {
                    device_id: id,
                    api_key: key
                },
                transports: ['polling']
            });

            socket.on('connect', () => {
                console.log('Connected to the server. Socket ID:', socket.id);
            });

            socket.on('ready', (result) => {
                $('#QrCode').attr('src', '/assets/img/qr.png')
                ListContainer.append('<li class="text-success fw-bold text-sm">Whatsapp Aktif (' + result.phone_number +
                    ')</li>')

                $('#HapusWa').show()
                $('#ScanWA').hide()

                if (!SAVED_ID) {
                    saveLocalSession(result.device_id, MASTER_KEY)
                }

                if ($('#ModalScanWA').hasClass('show')) {
                    Swal.fire('Berhasil', 'Whatsapp Aktif (' + result.phone_number + ')', 'success')
                    setTimeout(() => {
                        $('#ModalScanWA').modal('hide')
                    }, 1000)
                }
            })

            socket.on('qr', (result) => {
                console.log('QR Code Refreshed');
                $('#QrCode').attr('src', result.qr_image)
            })

            socket.on('status', (result) => {
                console.log('WA status:', result.status)
                if (result.status === 'disconnected') {
                    $('#Pesan').find('li').html(
                        '<span class="text-danger fw-bold">Terputus.</span> Sedang mencoba menghubungkan ulang...'
                    )
                }
            })

            socket.on('disconnect', () => {
                console.log('Socket disconnected');
            })
        }

        function restartGateway(id, key) {
            console.log('Requesting gateway restart for:', id);
            $.ajax({
                type: 'POST',
                url: API + '/api/devices/' + id + '/restart',
                headers: {
                    'x-api-key': MASTER_KEY
                },
                success: function(res) {
                    console.log('Gateway restart request sent:', res);
                },
                error: function(err) {
                    console.error('Failed to request gateway restart:', err);
                }
            });
        }

        $(document).ready(function() {
            CKEDITOR.replace('editor_spk');
            CKEDITOR.replace('editor_calk');

            $.ajax({
                type: 'GET',
                url: API + '/api/devices/' + CURRENT_ID,
                headers: {
                    'x-api-key': MASTER_KEY
                },
                success: function(result) {
                    if (result.success && result.device && (result.device.status === 'connected' || result.device
                            .phone_number)) {
                        $('#HapusWa').show()
                        $('#ScanWA').hide()
                        if (!SAVED_ID) {
                            saveLocalSession(result.device.id, MASTER_KEY)
                        }
                    } else {
                        $('#ScanWA').show()
                        $('#HapusWa').hide()
                    }
                },
                error: function() {
                    $('#ScanWA').show()
                    $('#HapusWa').hide()
                }
            })

            initSocket(CURRENT_ID, CURRENT_KEY);
        })

        $(document).on('click', '#RefreshQR', function(e) {
            e.preventDefault();
            const id = SAVED_ID || CURRENT_ID;
            restartGateway(id, MASTER_KEY);
            $(this).addClass('fa-spin');
            setTimeout(() => {
                $(this).removeClass('fa-spin');
            }, 2000);
            $('#Pesan').find('li').html('Menyegarkan Kode QR...')
        })

        $(document).on('click', '#ScanWA', function(e) {
            e.preventDefault()

            if (SAVED_ID) {

                restartGateway(SAVED_ID, MASTER_KEY);

                $('#ModalScanWA').modal('show')
                $('#Pesan').find('li').html('Sedang menyiapkan Kode QR...')
                return
            }

            Swal.fire({
                title: 'Aktivasi Whatsapp',
                text: 'Scan Whatsapp aplikasi LKM.',
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal',
                icon: 'info'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: API + '/api/devices',
                        headers: {
                            'x-api-key': MASTER_KEY
                        },
                        data: {
                            name: '{{ $kec->nama_lembaga_sort ?? 'LKM' }}'
                        },
                        success: function(result) {
                            if (result.success) {
                                saveLocalSession(result.device.id, result.device.api_key)
                                initSocket(result.device.id, result.device.api_key)
                                $('#ModalScanWA').modal('show')
                            }
                        }
                    })
                }
            })
        })

        $(document).on('click', '#HapusWa', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Hapus Whatsapp',
                text: 'Hapus koneksi whatsapp LKM.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                icon: 'error'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: API + '/api/devices/' + CURRENT_ID + '/logout',
                        headers: {
                            'x-api-key': MASTER_KEY
                        },
                        success: function() {
                            $.post('/pengaturan/whatsapp/delete_session', {
                                _token: '{{ csrf_token() }}'
                            }, function() {
                                window.location.reload()
                            })
                        },
                        error: function() {
                            $.post('/pengaturan/whatsapp/delete_session', {
                                _token: '{{ csrf_token() }}'
                            }, function() {
                                window.location.reload()
                            })
                        }
                    })
                }
            })
        })
    </script>

    <script>
        var tahun = "{{ date('Y') }}"
        var bulan = "{{ date('m') }}"

        $(".money").maskMoney();

        $(document).on('click', '.btn-simpan', async function(e) {
            e.preventDefault()

            if ($(this).attr('id') == 'SimpanSPK') {
                $('#spk').val(CKEDITOR.instances.editor_spk.getData())
            }

            if ($(this).attr('id') == 'SimpanCalk') {
                $('#custom_calk').val(CKEDITOR.instances.editor_calk.getData())
            }

            var form = $($(this).attr('data-target'))
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Toastr('success', result.msg)

                        if (result.nama_lembaga) {
                            $('#nama_lembaga_sort').html(result.nama_lembaga)
                        }
                    }
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static').addClass(
                            'is-invalid')
                        $('#msg_' + key).html(res)
                    })
                }
            })
        })

        $(document).on('click', '#EditLogo', function(e) {
            e.preventDefault()

            $('#logo_kec').trigger('click')
        })

        $(document).on('change', '#logo_kec', function(e) {
            e.preventDefault()

            var logo = $(this).get(0).files[0]
            if (logo) {
                var form = $('#FormLogo')
                var formData = new FormData(document.querySelector('#FormLogo'));
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        if (result.success) {
                            var reader = new FileReader();

                            reader.onload = function() {
                                $("#previewLogo").attr("src", reader.result);
                                $(".navbar-brand-img").attr("src", reader.result);
                                $(".colored-shadow").css('background-image',
                                    "url(" + reader.result + ")")
                            }

                            reader.readAsDataURL(logo);
                            Toastr('success', result.msg)
                        } else {
                            Toastr('error', result.msg)
                        }
                    }
                })
            }
        })
    </script>
@endsection
