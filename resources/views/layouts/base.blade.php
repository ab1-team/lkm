@php
    $wa_session = null;
    if (Session::has('lokasi')) {
        $wa_session = \App\Models\Whatsapp::where('lokasi', Session::get('lokasi'))->first();
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        body {
            opacity: 0 !important;
        }
    </style>
    <script>
        window.paceOptions = {
            ajax: false,
            document: true,
            eventLag: false,
            restartOnPushState: false,
            restartOnRequestAfter: false
        };
    </script>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="Sistem Informasi Unit Pengelola Kegiatan Berbasis Web">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="keywords"
        content="lkm, situnai, upk, online, siupk, upk online, siupk online, asta brata teknologi, abt">
    <meta name="author" content="Enfii">
    <meta name="msapplication-tap-highlight" content="no">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ Session::get('icon') }}">
    <link rel="icon" type="image/png" href="{{ Session::get('icon') }}">

    <title>{{ $title }} &mdash; Aplikasi LKM V.9.10</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="{{ asset('assets/argon/nucleo/css/nucleo.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('assets/argon/css/argon-dashboard.css') }}?v=2.1.0" rel="stylesheet" />

    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pe7-icon@1.0.4/dist/dist/pe-icon-7-stroke.css">

    <link rel="stylesheet" href="/assets/css/pace.css?v=1716515606">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/lkm-core.css">
    <link rel="stylesheet" href="/assets/css/lkm-menu.css">

    <script defer src="/assets/scripts/main.js"></script>
    <script defer src="/assets/scripts/demo.js"></script>
    <script defer src="/assets/scripts/toastr.js"></script>
    <script defer src="/assets/scripts/scrollbar.js"></script>
    <script defer src="/assets/scripts/fullcalendar.js"></script>
    <script defer src="/assets/scripts/maps.js"></script>
    <script defer src="/assets/scripts/chart_js.js"></script>

    @yield('style')


</head>

<body class="g-sidenav-show bg-gray-100">

    <div class="min-height-300 bg-dark position-absolute w-100"></div>

    @include('layouts.sidebar')

    
    <div class="navbar d-none">
        <div class="navbar-collapse"></div>
    </div>

    <main class="main-content position-relative border-radius-lg">

        @php
            $navPath = Request::path();
            $showIndividu = $navPath == 'transaksi/jurnal_angsuran_individu';
            $showKelompok = $navPath == 'transaksi/jurnal_angsuran';
        @endphp

        
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-2 mx-md-3 mx-lg-4 shadow-none border-radius-xl" id="navbarBlur"
            data-scroll="false" style="z-index: 1050; position: relative;">
            <div class="container-fluid py-1 px-3 align-items-center d-flex flex-wrap">

                
                <div class="me-3 d-flex align-items-center">
                    <button type="button" class="btn btn-sm p-2 shadow-none border-0" id="mobileSidenavToggle"
                        aria-label="Toggle sidebar" title="Menu" style="background: transparent; line-height: 1;">
                        <i class="fas fa-bars" style="color: #ffffff; font-size: 1.2rem;"></i>
                    </button>
                </div>

                
                <div class="d-flex flex-column justify-content-center me-auto {{ $showIndividu || $showKelompok ? 'order-last w-100 mt-2' : '' }}"
                    style="flex-shrink: 0;">
                    <h6 class="font-weight-bolder text-white mb-0">
                        {{ Session::get('nama_lembaga') }}
                    </h6>
                    <div class="text-white text-xs font-weight-bold opacity-9" style="margin-top: -2px;">
                        @if (Request::is('transaksi/jurnal_angsuran_individu'))
                            Jurnal Angsuran Individu
                        @elseif (Request::is('transaksi/jurnal_angsuran'))
                            Jurnal Angsuran Kelompok
                        @else
                            {{ ucwords(str_replace(['/', '_'], [' / ', ' '], Request::path())) }}
                        @endif
                    </div>
                </div>

                <div class="collapse navbar-collapse me-md-0 me-sm-4 align-items-center flex-grow-1" id="navbar">
                    
                    @if ($showIndividu || $showKelompok)
                        <div class="navbar-search-full me-3">
                            <div class="input-group shadow-none">
                                <span class="input-group-text">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                @if ($showIndividu)
                                    <input id="cariAnggota" type="text" class="form-control ps-2"
                                        placeholder="Cari Jurnal Angsuran Individu (Nama / NIK)" autocomplete="off">
                                @else
                                    <input id="cariKelompok" type="text" class="form-control ps-2"
                                        placeholder="Cari Jurnal Angsuran Kelompok (Kode / Nama)" autocomplete="off">
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="ms-md-auto"></div>

                    
                    <ul class="navbar-nav justify-content-end align-items-center">

                        
                        <li class="nav-item dropdown d-flex align-items-center ps-2">
                            <a href="javascript:;"
                                class="nav-link text-white font-weight-bold px-0 d-flex align-items-center gap-2"
                                id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                @php
                                    $foto = Session::get('foto');
                                    $jk = auth()->user()->jk ?? Session::get('jk');
                                    $defaultAvatar =
                                        $jk == 'P' ? asset('assets/argon/img/female.jpg') : asset('assets/argon/img/male.jpg');
                                    $fotoSrc = $foto ? asset('storage/profil/' . $foto) : $defaultAvatar;
                                @endphp
                                <img width="36" height="36"
                                    class="rounded-circle border border-white border-2" src="{{ $fotoSrc }}"
                                    id="profil_avatar" onerror="this.src='{{ $defaultAvatar }}'">
                                <span class="d-sm-inline d-none text-white font-weight-bold nama_user">
                                    {{ Session::get('nama') }}
                                </span>
                                <i class="fa fa-angle-down ms-1 opacity-8"></i>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4"
                                aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item border-radius-md" href="javascript:;" id="btnAcount">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="pe-7s-users me-2 fs-5"></i>
                                            <span>Account</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item border-radius-md" href="javascript:;"
                                        id="btnLaporanPelunasan">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="pe-7s-cloud-download me-2 fs-5"></i>
                                            <span>Reminder</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item border-radius-md" href="javascript:;" id="btnInvoiceTs">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="pe-7s-monitor me-2 fs-5"></i>
                                            <span>TS dan Invoice</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item border-radius-md" href="javascript:;">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="pe-7s-mail me-2 fs-5"></i>
                                            <span>Maintenance dan Server</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item border-radius-md" href="javascript:;" id="btnLaporanMou">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="pe-7s-comment me-2 fs-5"></i>
                                            <span>MoU</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item border-radius-md text-danger" href="javascript:;"
                                        id="logout">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="fas fa-sign-out-alt me-2 fs-5"></i>
                                            <span>Logout</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        
                        <li class="nav-item px-3 d-flex align-items-center">
                            <a href="/pengaturan/sop" class="nav-link text-white p-0">
                                <i class="fa fa-cog cursor-pointer"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        

        
        <form action="/pelaporan/preview" method="post" id="FormLaporanSisipan" target="_blank"
            style="display: none;">
            @csrf
            <input type="hidden" name="type" id="type_sisipan" value="pdf">
            <input type="hidden" name="tahun" id="tahun_sisipan" value="{{ date('Y') }}">
            <input type="hidden" name="bulan" id="bulan_sisipan" value="{{ date('m') }}">
            <input type="hidden" name="hari" id="hari_sisipan" value="{{ date('d') }}">
            <input type="hidden" name="laporan" id="laporan" value="pelunasan">
            <input type="hidden" name="sub_laporan" id="sub_laporan" value="">
        </form>

        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Scan Kartu Angsuran</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="reader"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-sm btn-info" id="stopScan">Stop</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid py-0 px-2 px-md-3 px-lg-4">
            @yield('content')
        </div>

    </main>

    <div class="fixed-plugin">
        <a href="/pengaturan/sop" class="text-dark position-fixed px-3 py-2"
            style="background: #fff; right: 30px; z-index: 990; box-shadow: 0 2px 12px 0 rgba(0,0,0,0.16); font-size: 1.25rem; cursor: pointer; bottom: 15px !important; border-radius: 50% !important; width: 50px !important; height: 50px !important; display: flex !important; align-items: center !important; justify-content: center !important;">
            <i class="fa fa-cog fa-spin py-2"></i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Argon Configurator</h5>
                    <p>See our dashboard options.</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0 overflow-auto">
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark" data-color="dark"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger"
                            onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 different sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white"
                        onclick="sidebarType(this)">White</button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default"
                        onclick="sidebarType(this)">Dark</button>
                </div>
                <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
                <div class="d-flex my-3">
                    <h6 class="mb-0">Navbar Fixed</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed"
                            onclick="navbarFixed(this)">
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4">
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version"
                            onclick="darkMode(this)">
                    </div>
                </div>
                <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/argon-dashboard">Free
                    Download</a>
                <a class="btn btn-outline-dark w-100"
                    href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard">View
                    documentation</a>
                <div class="w-100 text-center">
                    <a class="github-button" href="https://github.com/creativetimofficial/argon-dashboard"
                        data-icon="octicon-star" data-size="large" data-show-count="true"
                        aria-label="Star creativetimofficial/argon-dashboard on GitHub">Star</a>
                    <h6 class="mt-3">Thank you for sharing!</h6>
                    <a href="https://twitter.com/intent/tweet?text=Check%20Argon%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fargon-dashboard"
                        class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/argon-dashboard"
                        class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <form action="/logout" method="post" id="formLogout" style="display: none;">
        @csrf
    </form>

    @yield('modal')

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/argon/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/argon/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/argon/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/argon/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/argon/js/plugins/chartjs.min.js') }}"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="/assets/js/html5-qrcode.js?v=1716515606"></script>
    <script src="/assets/js/plugins/choices.min.js"></script>
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="//cdn.quilljs.com/1.3.7/quill.min.js"></script>

    
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"
        integrity="sha512-Rdk63VC+1UYzGSgd3u2iadi0joUrcwX0IWp2rTh6KXFoAmgOjRS99Vynz1lJPT8dLjvo6JZOqpAHJyfCEZ5KoA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"
        integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.min.js"></script>

    
    <script src="/assets/js/plugins/flatpickr.min.js"></script>

    
    

    @yield('script')
    
    <script>

        document.addEventListener('DOMContentLoaded', function() {
            if (
                !window.location.href.includes('/dashboard') &&
                {!! json_encode(Session::get('invoice') !== null) !!}
            ) {
                document.getElementById('formLogout').submit();
            }

            var mobileSidenavToggle = document.getElementById('mobileSidenavToggle');
            var sidenavMain = document.getElementById('sidenav-main');

            if (mobileSidenavToggle && sidenavMain) {

                var overlay = document.createElement('div');
                overlay.id = 'sidenavOverlay';
                overlay.className = 'sidenav-overlay d-xl-none';
                overlay.style.cssText =
                    'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; display: none; transition: opacity 0.3s ease; opacity: 0;';
                document.body.appendChild(overlay);

                function toggleSidenav() {
                    if (window.innerWidth >= 1200) {
                        document.body.classList.toggle('g-sidenav-hidden');
                    } else {
                        var isShowing = sidenavMain.classList.contains('show-mobile');
                        if (isShowing) {
                            sidenavMain.classList.remove('show-mobile');
                            document.body.classList.remove('g-sidenav-pinned');
                            overlay.style.opacity = '0';
                            setTimeout(() => overlay.style.display = 'none', 300);
                        } else {
                            sidenavMain.classList.add('show-mobile');
                            document.body.classList.add('g-sidenav-pinned');
                            overlay.style.display = 'block';
                            setTimeout(() => overlay.style.opacity = '1', 10);
                        }
                    }
                }

                mobileSidenavToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSidenav();
                });

                var iconSidenav = document.getElementById('iconSidenav');
                if (iconSidenav) {
                    iconSidenav.addEventListener('click', function(e) {
                        e.preventDefault();
                        toggleSidenav();
                    });
                }

                overlay.addEventListener('click', toggleSidenav);

                sidenavMain.addEventListener('click', function(e) {
                    if (window.innerWidth >= 1200) return;
                    if (e.target.closest('.nav-link:not(.menu-toggle)')) {
                        var isShowing = sidenavMain.classList.contains('show-mobile');
                        if (isShowing) toggleSidenav();
                    }
                });
            }

        });

        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        window.paceOptions = {
            ajax: true,
            document: false,
            eventLag: false,
            elements: {
                selectors: ['.g-sidenav-show']
            }
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        function Toastr(icon, text) {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            ctx.font = '1.2rem Nimrod MT';
            var width = Math.ceil(ctx.measureText(text).width) + 100;
            Toast.fire({
                icon,
                title: text,
                width
            });
        }

        function MultiToast(icon, text) {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            ctx.font = '1.2rem Nimrod MT';
            var width = Math.ceil(ctx.measureText(text).width) + 100;

            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            }).fire({
                icon,
                title: text,
                width
            });
        }

        function open_window(link) {
            return window.open(link);
        }

        $('#cariAnggota').typeahead({
            source: function(query, process) {
                return $.get('/perguliran/cari_anggota', {
                    query
                }, function(result) {
                    process(result.map(item => ({
                        id: item.id,
                        name: `${item.namadepan} [${item.domisi}, ${item.nama_desa}] - ${item.id} [${item.nik}]`,
                        value: item.id
                    })));
                });
            },
            afterSelect: function(item) {
                if (window.location.pathname.includes('jurnal_angsuran_individu')) {
                    $.get('/transaksi/form_angsuran_individu/' + item.id, function(result) {
                        var ch_pokok = document.getElementById('chartP').getContext('2d');
                        var ch_jasa = document.getElementById('chartJ').getContext('2d');
                        angsuran(true, result);
                        makeChart('pokok', ch_pokok, result.sisa_pokok, result.sum_pokok);
                        makeChart('jasa', ch_jasa, result.sisa_jasa, result.sum_jasa);
                        $('#loan-id').html(item.id);
                    });
                } else {
                    window.location.href = '/transaksi/jurnal_angsuran_individu?pinkel=' + item.id;
                }
            }
        });

        $('#cariKelompok').typeahead({
            source: function(query, process) {
                return $.get('/perguliran/cari_kelompok', {
                    query
                }, function(result) {
                    process(result.map(item => ({
                        id: item.id,
                        name: `${item.nama_kelompok} [${item.kd_kelompok}, ${item.nama_desa}] - Loan ID: ${item.id}`,
                        value: item.id
                    })));
                });
            },
            afterSelect: function(item) {
                if (window.location.pathname.includes('jurnal_angsuran')) {
                    $.get('/transaksi/form_angsuran/' + item.id, function(result) {
                        $('#loan-id').html(item.id);
                        $('#id').val(item.id);
                        $('#_pokok').val(result.sisa_pokok);
                        $('#_jasa').val(result.sisa_jasa);
                        $('#alokasi_pokok').html('Rp. ' + formatter.format(result.alokasi_pokok));
                        $('#alokasi_jasa').html('Rp. ' + formatter.format(result.alokasi_jasa));

                        if (typeof chartP !== 'undefined') chartP.destroy();
                        if (typeof chartJ !== 'undefined') chartJ.destroy();

                        chartP = new Chart(document.getElementById('chartP').getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Pokok', 'Sisa'],
                                datasets: [{
                                    label: 'Rp. ',
                                    data: [result.sum_pokok, result.sisa_pokok],
                                    backgroundColor: ['rgb(54, 162, 235)',
                                        'rgb(255, 99, 132)'
                                    ],
                                    hoverOffset: 4
                                }]
                            }
                        });

                        chartJ = new Chart(document.getElementById('chartJ').getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Jasa', 'Sisa'],
                                datasets: [{
                                    label: 'Rp. ',
                                    data: [result.sum_jasa, result.sisa_jasa],
                                    backgroundColor: ['rgb(255, 205, 86)',
                                        'rgb(255, 99, 132)'
                                    ],
                                    hoverOffset: 4
                                }]
                            }
                        });

                        $('#pokok').val(formatter.format(result.saldo_pokok));
                        $('#jasa').val(formatter.format(result.saldo_jasa));
                        $('#pokok, #jasa, #denda').trigger('change');
                    });
                } else {
                    window.location.href = '/transaksi/jurnal_angsuran?pinkel=' + item.id;
                }
            }
        });

        function makeChart(id, target, sisa_saldo, sum_saldo) {
            window['chr_' + id] = new Chart(target, {
                type: 'doughnut',
                data: {
                    labels: ['Sisa Saldo', 'Total Pengembalian'],
                    datasets: [{
                        data: [sisa_saldo, sum_saldo],
                        backgroundColor: ['#5e72e4', '#f5365c'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function angsuran(destroy = false, result) {
            $('#pokok').val(formatter.format(result.saldo_pokok));
            $('#jasa').val(formatter.format(result.saldo_jasa));
            $('#id').val(result.pinkel.id);
            $('#_pokok').val(result.sisa_pokok);
            $('#_jasa').val(result.sisa_jasa);

            if (destroy) {
                if (typeof chr_pokok !== 'undefined' && chr_pokok) chr_pokok.destroy();
                if (typeof chr_jasa !== 'undefined' && chr_jasa) chr_jasa.destroy();
            }

            $('#alokasi_pokok').html('Rp. ' + formatter.format(result.alokasi_pokok));
            $('#alokasi_jasa').html('Rp. ' + formatter.format(result.alokasi_jasa));
            $('#pokok, #jasa, #denda').trigger('change');
        }

        $('#logout').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Keluar',
                text: 'Dengan mengklik tombol logout maka Anda tidak bisa membuka halaman ini lagi sebelum melakukan login ulang, Logout?',
                showCancelButton: true,
                confirmButtonText: 'Keluar',
                cancelButtonText: 'Batal',
                icon: 'question',
                customClass: {
                    container: 'notranslate'
                }
            }).then(result => {
                if (result.isConfirmed) {
                    $('#formLogout').submit();
                }
            });
        });

        $('#btnLaporanPelunasan').on('click', function(e) {
            e.preventDefault();
            $('input#laporan').val('pelunasan');
            $('#FormLaporanSisipan').submit();
        });

        $('#btnAcount').on('click', function(e) {
            e.preventDefault();
            window.open('/profil');
        });

        $('#btnInvoiceTs').on('click', function(e) {
            e.preventDefault();
            window.open('/pelaporan/ts');
        });

        $('#btnLaporanMou').click(function(e) {
            e.preventDefault();
            window.open('/pelaporan/mou');
        });

        $(document).ready(function() {
            const gatewayUrl = "{{ env('APP_API') }}";
            const deviceId = "{{ $wa_session->device_id ?? '' }}";
            const deviceKey = "{{ $wa_session->device_key ?? '' }}";

            if (!gatewayUrl || !deviceId || !deviceKey) return;

            const waSocket = io(gatewayUrl, {
                query: {
                    device_id: deviceId,
                    api_key: deviceKey
                },
                transports: ['polling']
            });

            waSocket.on('message_sent', (res) => {
                if (res.device_id === deviceId) {
                    if (window.location.pathname.indexOf('/pengaturan/whatsapp') === -1) {
                        Toastr('success', `WA: Pesan terkirim ke ${res.recipient}`);
                    }
                }
            });

            waSocket.on('message_failed', (res) => {
                if (res.device_id === deviceId) {
                    Toastr('error', `WA: Gagal ke ${res.recipient}: ${res.error}`);
                }
            });

            waSocket.on('ready', (res) => {
                if (window.location.pathname.indexOf('/pengaturan/whatsapp') !== -1) {
                    MultiToast('success', `WhatsApp Aktif (${res.phone_number})`);
                }
            });

            waSocket.on('status', (res) => {
                if (res.status === 'disconnected' || res.status === 'close') {
                    MultiToast('warning', `WhatsApp Terputus!`);
                }
            });
        });
    </script>

    <script>

        tinymce.init({
            selector: '.tiny-mce-editor',
            plugins: 'table visualblocks fullscreen',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | align | table fullscreen | removeformat',
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace;',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'ARAFII'
        });

        if (navigator.platform.indexOf('Win') > -1 && document.querySelector('#sidenav-scrollbar')) {
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), {
                damping: '0.5'
            });
        }
    </script>

    
    @if (session('pesan'))
        <script>
            Toastr('success', "{{ session('pesan') }}")
        </script>
    @endif

    
    <script>
        document.querySelectorAll('.copyright-year').forEach(el => {
            el.textContent = new Date().getFullYear();
        });
    </script>

    
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    
    <script src="{{ asset('assets/argon/js/argon-dashboard.min.js') }}?v=2.1.0"></script>

    
    
    <script>
        $(document).ready(function() {
            var $lastClickedBtn = null;

            $(document).on('click', 'button, input[type="submit"], input[type="button"], .btn', function() {
                $lastClickedBtn = $(this);
            });

            $(document).ajaxSend(function(event, xhr, settings) {
                if ($lastClickedBtn && $lastClickedBtn.length) {
                    var text = $lastClickedBtn.text().toLowerCase().trim();
                    var id = ($lastClickedBtn.attr('id') || '').toLowerCase();

                    if (text.indexOf('batal') !== -1 || text.indexOf('tutup') !== -1 || text.indexOf(
                            'close') !== -1 || $lastClickedBtn.hasClass('btn-close') || $lastClickedBtn
                        .attr('data-bs-dismiss')) {
                        return;
                    }

                    var isActionBtn = $lastClickedBtn.attr('type') === 'submit' ||
                        $lastClickedBtn.hasClass('btn-primary') ||
                        $lastClickedBtn.hasClass('btn-success') ||
                        $lastClickedBtn.hasClass('btn-info') ||
                        text.indexOf('simpan') !== -1 ||
                        text.indexOf('proses') !== -1 ||
                        text.indexOf('cetak') !== -1 ||
                        text.indexOf('register') !== -1 ||
                        text.indexOf('daftar') !== -1 ||
                        text.indexOf('save') !== -1 ||
                        id.indexOf('simpan') !== -1 ||
                        id.indexOf('register') !== -1 ||
                        id.indexOf('submit') !== -1;

                    if (isActionBtn && !$lastClickedBtn.hasClass('btn-loading-state')) {
                        $lastClickedBtn.data('original-html', $lastClickedBtn.html());
                        $lastClickedBtn.prop('disabled', true);
                        $lastClickedBtn.addClass('btn-loading-state');

                        var originalHtml = $lastClickedBtn.data('original-html') || '';
                        $lastClickedBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> ' + (originalHtml
                            .indexOf('fa-') > -1 ? '' : 'Loading...'));
                    }
                }
            });

            $(document).ajaxComplete(function() {
                $('.btn-loading-state').each(function() {
                    var $btn = $(this);
                    if ($btn.data('original-html')) {
                        $btn.html($btn.data('original-html'));
                    }
                    $btn.prop('disabled', false);
                    $btn.removeClass('btn-loading-state');
                });
                $lastClickedBtn = null;
            });

            $(document).on('submit', 'form', function(e) {
                var $form = $(this);

                if ($form.attr('target') === '_blank') {
                    return true;
                }

                var $btn = $form.find('button[type="submit"], input[type="submit"]');
                if ($btn.hasClass('btn-loading-state')) {
                    e.preventDefault();
                    return false;
                }

                $btn.each(function() {
                    var $thisBtn = $(this);
                    $thisBtn.data('original-html', $thisBtn.html());
                    $thisBtn.prop('disabled', true);
                    $thisBtn.addClass('btn-loading-state');
                    $thisBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');
                });

                setTimeout(function() {
                    $btn.each(function() {
                        var $b = $(this);
                        if ($b.hasClass('btn-loading-state')) {
                            $b.html($b.data('original-html'));
                            $b.prop('disabled', false);
                            $b.removeClass('btn-loading-state');
                        }
                    });
                }, 15000);
            });
        });
    </script>

    <script>
        (function() {
            var isLoaded = false;

            function showPage() {
                if (isLoaded) return;
                isLoaded = true;
                document.body.classList.add('loaded');
            }
            window.addEventListener('load', showPage);
            setTimeout(showPage, 300);
        })();
    </script>
</body>

</html>
