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
                                    <a role="tab" class="btn btn-white settings-nav-item" data-bs-toggle="tab" href="#tab-content-ttd-qr">
                                        <i class="fa-solid fa-signature"></i>
                                        <span>Tanda Tangan Gambar</span>
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

                            <div class="tab-pane tabs-animation fade" id="tab-content-ttd-qr" role="tabpanel">
                                <div class="row">
                                    <div class="main-card mb-3 card">
                                        <div class="card-body">
                                            <h5 class="card-title">Upload Gambar Tanda Tangan</h5>
                                            <p class="text-muted small mb-3">
                                                Gambar ini akan ditampilkan sebagai tanda tangan Direksi Lembaga di blok tanda tangan dokumen-dokumen pinjaman (SPK, Perjanjian, Kuitansi, dll).
                                            </p>
                                            @include('sop.partials._ttd_qr')
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
    <style>
        .swal-wa-title-small {
            font-size: 13px !important;
            font-weight: 600 !important;
            text-transform: lowercase !important;
            letter-spacing: 0 !important;
            color: #888 !important;
            margin-bottom: 8px !important;
        }
        .swal2-popup.swal2-popup-wa-error {
            background: #1f1f1f !important;
            color: #fff !important;
        }
        .swal2-popup.swal2-popup-wa-error .swal2-icon {
            margin-top: 12px !important;
        }
    </style>
    <script src="/vendor/ckeditor/ckeditor.js"></script>

    <script>
        let ListContainer = $('#Pesan')
        const API = '{{ $api }}'
        const MASTER_KEY = '{{ $api_key }}'
        const SAVED_INSTANCE = @json($instance_name)

        let pollInterval = null
        let qrPollInterval = null

        function setIdleState() {
            console.log('[WA] setIdleState - SAVED_INSTANCE =', SAVED_INSTANCE)
            $('#HapusWa, #ScanWA').hide()
            $('#CreateInstance').show()
            ListContainer.html('<li>Pastikan WhatsApp Gateway menyala.</li>')
        }

        function setActiveState(instance) {
            console.log('[WA] setActiveState -', instance)
            $('#CreateInstance, #ScanWA').hide()
            $('#HapusWa').show()
            ListContainer.html('<li class="text-success fw-bold text-sm">Whatsapp Aktif (' + instance + ')</li>')
        }

        function setPendingState(instance) {
            console.log('[WA] setPendingState -', instance)
            $('#CreateInstance').hide()
            $('#HapusWa, #ScanWA').show()
            ListContainer.html('<li>Menunggu koneksi ke WhatsApp...</li>')
        }

        function pollConnectionState(instance) {
            pollInterval = setInterval(() => {
                $.ajax({
                    type: 'GET',
                    url: '/pengaturan/whatsapp/connection_state',
                    success: function(res) {
                        console.log('Connection state:', res)
                        if (res.state === 'open') {
                            clearInterval(pollInterval)
                            ListContainer.html('<li class="text-success fw-bold text-sm">Whatsapp Aktif</li>')
                            $('#QrCode').attr('src', '/assets/img/qr.png')
                            setActiveState(instance)
                            Toastr('success', 'WhatsApp berhasil terhubung')

                            setTimeout(() => {
                                if ($('#ModalScanWA').hasClass('show')) {
                                    $('#ModalScanWA').modal('hide')
                                }
                            }, 1000)
                        } else if (res.state === 'close' || res.state === 'refused') {
                            clearInterval(pollInterval)
                            Toastr('error', 'Koneksi ditutup oleh gateway')
                        }
                    },
                    error: function() {
                        console.warn('Polling error')
                    }
                })
            }, 3000)
        }

        function pollQr() {
            if (qrPollInterval) {
                clearInterval(qrPollInterval)
            }

            let attempts = 0
            qrPollInterval = setInterval(() => {
                attempts++
                if (attempts > 30) {
                    clearInterval(qrPollInterval)
                    return
                }

                $.ajax({
                    type: 'GET',
                    url: '/pengaturan/whatsapp/qr',
                    success: function(res) {
                        console.log('QR poll:', res)
                        if (!res.success) {
                            clearInterval(qrPollInterval)
                            ListContainer.html('<li class="text-danger fw-bold text-sm">' + (res.msg || 'Gagal memuat QR dari gateway.') + '</li>')
                            Toastr('error', res.msg || 'Gagal memuat QR dari gateway.')
                            return
                        }
                        if (res.qr) {
                            clearInterval(qrPollInterval)
                            $('#QrCode').attr('src', res.qr.startsWith('data:') ? res.qr : 'data:image/png;base64,' + res.qr)
                            ListContainer.html('<li class="text-success fw-bold text-sm">Scan QR dari WhatsApp</li>')
                            Toastr('success', 'QR siap, silakan scan dari WhatsApp')
                        }
                    },
                    error: function() {
                        console.warn('QR poll error')
                    }
                })
            }, 2000)
        }

        $(document).ready(function() {
            CKEDITOR.replace('editor_spk');
            CKEDITOR.replace('editor_calk');

            window.escapeHtml = function(str) {
                if (str === null || str === undefined) return ''
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
            }

            if (SAVED_INSTANCE) {
                $.ajax({
                    type: 'GET',
                    url: '/pengaturan/whatsapp/connection_state',
                    success: function(res) {
                        console.log('Initial state:', res)
                        if (res.state === 'open') {
                            setActiveState(SAVED_INSTANCE)
                        } else {
                            setPendingState(SAVED_INSTANCE)
                        }
                    },
                    error: function() {
                        setPendingState(SAVED_INSTANCE)
                    }
                })
            } else {
                setIdleState()
            }
        })

        $(document).on('click', '#CreateInstance', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Aktivasi WhatsApp',
                text: 'Buat instance WhatsApp baru untuk kecamatan ini.',
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal',
                icon: 'info'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '/pengaturan/whatsapp/save_device',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            console.log('Create instance response:', res)
                            if (res.success) {
                                if (res.qr) {
                                    $('#QrCode').attr('src', res.qr.startsWith('data:') ? res.qr : 'data:image/png;base64,' + res.qr)
                                    ListContainer.html('<li class="text-success fw-bold text-sm">Scan QR dari WhatsApp</li>')
                                } else {
                                    $('#QrCode').attr('src', '/assets/img/qr.png')
                                    ListContainer.html('<li>Menunggu QR dari gateway...</li>')
                                }

                                setPendingState(res.instance)
                                $('#ModalScanWA').modal('show')
                                pollConnectionState(res.instance)
                                if (! res.qr) {
                                    pollQr()
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'error',
                                    html:
                                        '<div style="background:#000;color:#fff;font-size:12px;padding:8px 10px;border-radius:4px;text-align:left;line-height:1.4;word-break:space;white-space:pre-wrap;">'+
                                        escapeHtml(res.msg || 'Gagal membuat instance.')+
                                        '</div>',
                                    customClass: { title: 'swal-wa-title-small', popup: 'swal2-popup-wa-error' }
                                })
                            }
                        },
                        error: function(xhr) {
                            console.error('[WA] /save_device error:', xhr)
                            var body = null
                            try {
                                body = xhr && xhr.responseText ? JSON.parse(xhr.responseText) : null
                            } catch (e) {
                                body = xhr && xhr.responseText ? xhr.responseText : null
                            }
                            var headline = 'Gagal terhubung ke gateway Evolution.'
                            if (xhr && xhr.status) {
                                headline += ' (HTTP ' + xhr.status + ')'
                            }
                            var reason = ''
                            if (body && (body.msg || body.message)) {
                                reason = String(body.msg || body.message)
                            } else if (xhr && xhr.statusText) {
                                reason = String(xhr.statusText)
                            }
                            var reasonBlock = reason
                                ? '<div style="background:#000;color:#9cdcfe;font-size:11px;padding:8px 10px;border-radius:4px;text-align:left;line-height:1.4;word-break:space;white-space:pre-wrap;margin-top:6px;max-height:220px;overflow:auto;">'+escapeHtml(reason)+'</div>'
                                : ''
                            Swal.fire({
                                icon: 'error',
                                title: 'error',
                                html:
                                    '<div style="background:#000;color:#fff;font-size:12px;padding:8px 10px;border-radius:4px;text-align:left;line-height:1.4;word-break:space;white-space:pre-wrap;">'+
                                    escapeHtml(headline)+'</div>'+reasonBlock,
                                customClass: { title: 'swal-wa-title-small', popup: 'swal2-popup-wa-error' }
                            })
                        }
                    })
                }
            })
        })

        $(document).on('click', '#ScanWA', function(e) {
            e.preventDefault()
            console.log('[WA] #ScanWA clicked - SAVED_INSTANCE =', SAVED_INSTANCE)

            if (!SAVED_INSTANCE) {
                Toastr('error', 'Instance belum dibuat. Klik "Buat Instance" terlebih dahulu.')
                return
            }

            $('#ModalScanWA').modal('show')
            $('#QrCode').attr('src', '/assets/img/qr.png')
            ListContainer.html('<li>Menunggu QR dari gateway...</li>')

            $.ajax({
                type: 'GET',
                url: '/pengaturan/whatsapp/qr',
                success: function(res) {
                    console.log('[WA] /qr response:', res)
                    if (!res.success) {
                        ListContainer.html('<li class="text-danger fw-bold text-sm">' + (res.msg || 'Gagal memuat QR dari gateway.') + '</li>')
                        Toastr('error', res.msg || 'Gagal memuat QR dari gateway.')
                        return
                    }
                    if (res.qr) {
                        $('#QrCode').attr('src', res.qr.startsWith('data:') ? res.qr : 'data:image/png;base64,' + res.qr)
                        ListContainer.html('<li class="text-success fw-bold text-sm">Scan QR dari WhatsApp</li>')
                    } else {
                        ListContainer.html('<li>Menunggu QR dari gateway...</li>')
                        pollQr()
                    }
                    pollConnectionState(SAVED_INSTANCE)
                },
                error: function(xhr) {
                    console.error('[WA] /qr error:', xhr)
                    ListContainer.html('<li class="text-danger fw-bold text-sm">Gagal terhubung ke gateway (' + xhr.status + ').</li>')
                    Toastr('error', 'Gagal terhubung ke gateway (' + xhr.status + ').')
                }
            })
        })

        $(document).on('click', '#RefreshQR', function(e) {
            e.preventDefault()
            if (!SAVED_INSTANCE) return
            $(this).addClass('fa-spin')
            setTimeout(() => {
                $(this).removeClass('fa-spin')
            }, 2000)
            ListContainer.html('Menyegarkan Kode QR...')

            $.ajax({
                type: 'GET',
                url: '/pengaturan/whatsapp/qr',
                success: function(res) {
                    if (!res.success) {
                        ListContainer.html('<li class="text-danger fw-bold text-sm">' + (res.msg || 'Gagal memuat QR dari gateway.') + '</li>')
                        Toastr('error', res.msg || 'Gagal memuat QR dari gateway.')
                        return
                    }
                    if (res.qr) {
                        $('#QrCode').attr('src', res.qr.startsWith('data:') ? res.qr : 'data:image/png;base64,' + res.qr)
                        ListContainer.html('<li class="text-success fw-bold text-sm">Scan QR dari WhatsApp</li>')
                    } else {
                        ListContainer.html('<li>QR belum tersedia, coba lagi beberapa detik.</li>')
                    }
                },
                error: function(xhr) {
                    ListContainer.html('<li class="text-danger fw-bold text-sm">Gagal terhubung ke gateway (' + xhr.status + ').</li>')
                    Toastr('error', 'Gagal terhubung ke gateway (' + xhr.status + ').')
                }
            })
        })

        $(document).on('click', '#HapusWa', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Hapus WhatsApp',
                text: 'Hapus koneksi WhatsApp LKM.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                icon: 'error'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (pollInterval) clearInterval(pollInterval)
                    $.post('/pengaturan/whatsapp/delete_session', {
                        _token: '{{ csrf_token() }}'
                    }, function() {
                        window.location.reload()
                    }).fail(function() {
                        window.location.reload()
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

            if ($(this).attr('id') == 'SimpanTtdQr') {
                return
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

        $(document).on('click', '#EditTtdQrDropzone', function(e) {
            e.preventDefault()
            $('#gambar_ttd').trigger('click')
        })

        var $dropzone = $('#EditTtdQrDropzone')
        $dropzone.on('dragover', function(e) {
            e.preventDefault()
            e.stopPropagation()
            $(this).addClass('dragover')
        })
        $dropzone.on('dragleave', function(e) {
            e.preventDefault()
            e.stopPropagation()
            $(this).removeClass('dragover')
        })
        $dropzone.on('drop', function(e) {
            e.preventDefault()
            e.stopPropagation()
            $(this).removeClass('dragover')
            var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files
            if (files && files.length > 0) {
                var dt = new DataTransfer()
                dt.items.add(files[0])
                document.getElementById('gambar_ttd').files = dt.files
                $('#gambar_ttd').trigger('change')
            }
        })

        $(document).on('change', '#gambar_ttd', function() {
            var file = $(this).get(0).files[0]
            var $pending = $('#pendingFileInfo')
            var $preview = $('#previewTtdQr')
            if (!file) {
                $pending.addClass('d-none')
                return
            }
            var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png']
            if (allowedTypes.indexOf(file.type) === -1) {
                Toastr('error', 'Format harus JPG, JPEG, atau PNG.')
                $(this).val('')
                $pending.addClass('d-none')
                return
            }
            if (file.size > 4 * 1024 * 1024) {
                Toastr('error', 'Ukuran maksimum 4MB.')
                $(this).val('')
                $pending.addClass('d-none')
                return
            }
            var reader = new FileReader()
            reader.onload = function(e) {
                $preview.attr('src', e.target.result)
                $pending.removeClass('d-none')
            }
            reader.readAsDataURL(file)
        })

        $(document).on('click', '#SimpanTtdQr', function(e) {
            e.preventDefault()
            e.stopImmediatePropagation()

            var $form = $('#FormTtdQr')
            var $btn = $(this)
            var originalHtml = $btn.html()

            var fileInput = document.getElementById('gambar_ttd')
            var hasFile = fileInput && fileInput.files && fileInput.files.length > 0

            if (!hasFile) {
                var $warn = $('#pendingFileInfo')
                if ($warn.length && !$warn.hasClass('d-none')) {
                    Toastr('error', 'Batalkan dulu gambar baru, atau klik Simpan untuk mengunggahnya.')
                    return
                }
            }

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Menyimpan...')

            var formData = new FormData($form[0])
            $.ajax({
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').first().val() },
                success: function(result) {
                    if (result.success) {
                        Toastr('success', result.msg || 'Berhasil disimpan')

                        if (result.url) {
                            var freshUrl = result.url.split('?')[0] + '?t=' + Date.now()
                            $('#previewTtdQr').attr('src', freshUrl)
                        }

                        $('#gambar_ttd').val('')
                        $('#pendingFileInfo').addClass('d-none')

                        $('.logo-preview-wrapper').css('border-color', '#22c55e')
                        $('#HapusTtdQr').prop('disabled', false).attr('title', 'Hapus gambar')

                        var withName = !!result.with_name
                        var ext = (result.path || '').split('.').pop() || 'png'
                        var id = {{ $kec->id }}
                        var fname = withName ? (id + '-name.' + ext) : (id + '.' + ext)
                        var suffix = withName ? ' (dengan nama penandatangan di bawah)' : ''
                        $('#pathInfoTtd').html('File aktif: <code>storage/app/public/qr/' + fname + '</code>' + suffix)

                        $('.logo-hover-overlay span').text('Klik atau jatuhkan file untuk mengganti')
                        $('.logo-upload-container').removeClass('d-none')
                        $('.alert-warning').addClass('d-none')
                    } else {
                        Toastr('error', result.msg || 'Gagal menyimpan')
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.msg) || 'Gagal menyimpan'
                    Toastr('error', msg)
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalHtml)
                }
            })
        })

        $(document).on('click', '#HapusTtdQr', function(e) {
            e.preventDefault()
            if (!confirm('Hapus gambar tanda tangan untuk lokasi ini? Dokumen akan kembali menampilkan area kosong.')) return

            $.ajax({
                type: 'DELETE',
                url: '/pengaturan/ttd-qr/' + {{ $kec->id }},
                data: { _token: $('input[name="_token"]').first().val() },
                success: function(result) {
                    if (result.success) {
                        Toastr('success', result.msg || 'Berhasil dihapus')

                        $('#previewTtdQr').attr('src', '/assets/img/qr.png?t=' + Date.now())
                        $('#pathInfoTtd').html('Pilih gambar untuk dipratinjau. Klik <strong>Simpan Perubahan</strong> untuk mengunggah.')
                        $('#pendingFileInfo').addClass('d-none')
                        $('#gambar_ttd').val('')
                        $('#dengan_nama').prop('checked', true)

                        $('.logo-preview-wrapper').css('border-color', '#cbd5e1')
                        $('#HapusTtdQr').prop('disabled', true).attr('title', 'Tidak ada gambar untuk dihapus')
                        $('.logo-hover-overlay span').text('Klik atau jatuhkan file untuk mengunggah')
                        $('.alert-warning').removeClass('d-none')
                    } else {
                        Toastr('error', result.msg || 'Gagal hapus')
                    }
                },
                error: function() {
                    Toastr('error', 'Gagal menghapus')
                }
            })
        })
    </script>
@endsection
