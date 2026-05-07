@extends('layouts.base')

@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    {{-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"> --}}
    <style>
        /* Modern Sidebar Navigation Styling */
        .settings-sidebar-card {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 16px !important;
            background-color: #323b44 !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
            overflow: hidden !important;
            padding: 16px 12px !important;
        }

        .settings-sidebar-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #94a3b8;
            font-weight: 800;
            padding: 12px 16px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-sidebar-title i {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.1rem;
        }

        .settings-nav-list {
            display: flex;
            flex-direction: column;
            gap: 0px;
            padding-left: 0;
            list-style: none;
            margin-bottom: 0;
        }

        .settings-nav-list .mb-3 {
            margin-bottom: 0px !important;
        }

        .settings-nav-item {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 16px !important;
            padding: 10px 16px !important;
            color: #cbd5e1 !important;
            background-color: transparent !important;
            background: transparent !important;
            border: none !important;
            border-radius: 12px !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            cursor: pointer !important;
            width: 100% !important;
            box-shadow: none !important;
        }

        /* Hover State */
        .settings-nav-item:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(4px) !important;
            box-shadow: none !important;
        }

        /* Active State */
        .settings-nav-item.active {
            color: #2563eb !important;
            background-color: #ffffff !important;
            background: #ffffff !important;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.12), 0 4px 8px -4px rgba(37, 99, 235, 0.08) !important;
        }

        .settings-nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            height: 70%;
            width: 4px;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: all 0.25s ease;
        }

        .settings-nav-item.active::before {
            opacity: 1;
        }

        .settings-nav-item i {
            font-size: 1.1rem !important;
            width: 24px !important;
            margin-right: 16px !important;
            color: #94a3b8;
            transition: all 0.25s ease;
        }

        .settings-nav-item:hover i {
            color: #3b82f6;
        }

        .settings-nav-item.active i {
            color: #2563eb;
            transform: scale(1.1);
        }

        .settings-nav-item span {
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.2px;
        }

        @media (min-width: 768px) {
            .settings-sidebar-col {
                flex: 0 0 29% !important;
                max-width: 29% !important;
            }
            .settings-content-col {
                flex: 0 0 71% !important;
                max-width: 71% !important;
            }
        }
    </style>

    <div class="app-main__inner">

        <div class="tab-content">
            <div class="tab-pane fade show active" id="" role="tabpanel">
                <div class="row">
                    <div class="col-md-3 settings-sidebar-col">
                        <div class="main-card mb-3 card settings-sidebar-card">
                            <ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav flex-column settings-nav-list">
                                <div class="settings-sidebar-title">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>Pengaturan SOP</span>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item active" id="wellcome" data-bs-toggle="tab" href="#tab-content-0">
                                        <i class="fa-solid fa-home"></i>
                                        <span>Wellcome</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="lembaga" data-bs-toggle="tab" href="#tab-content-1">
                                        <i class="fa-solid fa-tree-city"></i>
                                        <span>Identitas Lembaga</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="pengelola" data-bs-toggle="tab" href="#tab-content-2">
                                        <i class="fa-solid fa-person-chalkboard"></i>
                                        <span>Sebutan Pengelola</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="peminjam" data-bs-toggle="tab" href="#tab-content-3">
                                        <i class="fa-solid fa-chart-simple"></i>
                                        <span>Sistem Pinjaman</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-9">
                                        <i class="fa-solid fa-square-poll-horizontal"></i>
                                        <span>Kolektibilitas</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="simpanan" data-bs-toggle="tab" href="#tab-content-8">
                                        <i class="fa-solid fa-vault"></i>
                                        <span>Sistem Simpanan</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" id="asuransi" data-bs-toggle="tab" href="#tab-content-4">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                        <span>Pengaturan Asuransi</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-5">
                                        <i class="fa-solid fa-laptop-file"></i>
                                        <span>Redaksi SPK</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-kustomisasi-calk">
                                        <i class="fa-solid fa-laptop-file"></i>
                                        <span>Kustomisasi CALK</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-6">
                                        <i class="fa-solid fa-panorama"></i>
                                        <span>Logo</span>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-7">
                                        <i class="fa-solid fa-camera-rotate"></i>
                                        <span>Whatsapp</span>
                                    </a>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-9 settings-content-col">
                        <div class="tab-content">
                            <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Wellcome !!</h5>
                                            @include('sop.partials._wellcome')
                                        </div>
                                    </div>
                                </div>
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

            // Cek status saat ini ke gateway
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
                // Pemicu restart otomatis jika session sudah ada tapi tidak aktif
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
