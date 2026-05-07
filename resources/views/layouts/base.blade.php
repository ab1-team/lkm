@php
    $wa_session = null;
    if (Session::has('lokasi')) {
        $wa_session = \App\Models\Whatsapp::where('lokasi', Session::get('lokasi'))->first();
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <style>body { opacity: 0 !important; }</style>
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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="keywords" content="lkm, situnai, upk, online, siupk, upk online, siupk online, asta brata teknologi, abt">
    <meta name="author" content="Enfii">
    <meta name="msapplication-tap-highlight" content="no">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ Session::get('icon') }}">
    <link rel="icon" type="image/png" href="{{ Session::get('icon') }}">

    <title>{{ $title }} &mdash; Aplikasi LKM V.9.10</title>

    {{-- ===================== FONTS & ICONS ===================== --}}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="{{ asset('argon/nucleo/css/nucleo.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('argon/css/argon-dashboard.css') }}?v=2.1.0" rel="stylesheet" />

    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    {{-- ===================== SELECT2 ===================== --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

  <!-- jsTree -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />

  <!-- Quill -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" />



  <!-- jQuery UI -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

  <!-- Flatpickr -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <!-- Pe7 Icon -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pe7-icon@1.0.4/dist/dist/pe-icon-7-stroke.css">

    {{-- ===================== LOCAL CSS ===================== --}}
    <link rel="stylesheet" href="/assets/css/pace.css?v=1716515606">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- ===================== DEFERRED LOCAL SCRIPTS ===================== --}}
    <script defer src="/assets/scripts/main.js"></script>
    <script defer src="/assets/scripts/demo.js"></script>
    <script defer src="/assets/scripts/toastr.js"></script>
    <script defer src="/assets/scripts/scrollbar.js"></script>
    <script defer src="/assets/scripts/fullcalendar.js"></script>
    <script defer src="/assets/scripts/maps.js"></script>
    <script defer src="/assets/scripts/chart_js.js"></script>

    @yield('style')

    <style>
        /* ===================== MOBILE SIDENAV ===================== */
        @media (max-width: 1199.98px) {
            #sidenav-main {
                transform: translateX(-300px);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                z-index: 1050;
                position: fixed !important;
            }

            #sidenav-main.show-mobile,
            body.g-sidenav-pinned #sidenav-main {
                transform: translateX(0);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35) !important;
            }

            .main-content {
                margin-left: 0 !important;
            }
        }

        /* ===================== FADE-IN FOUC PREVENTER ===================== */
        body {
            opacity: 0;
            transition: opacity 0.15s ease-in-out;
        }
        body.loaded {
            opacity: 1 !important;
        }

        /* ===================== UNIFIED CARD & TYPOGRAPHY SYSTEM ===================== */
        .card {
            border-radius: 12px !important;
            box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02) !important;
            border: 1px solid rgba(0, 0, 0, 0.07) !important;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out !important;
        }
        .card:hover {
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08), 0 8px 8px -4px rgba(0, 0, 0, 0.03) !important;
        }
        .card-header, .card-footer {
            border-radius: 12px !important;
        }
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
            font-family: 'Open Sans', sans-serif !important;
            font-weight: 600 !important;
            color: #344767 !important;
            letter-spacing: -0.025em !important;
        }
        body, p, span, td, th, li, a {
            font-family: 'Open Sans', sans-serif !important;
            letter-spacing: -0.01em !important;
        }

        /* ===================== UNIFIED BUTTON SYSTEM ===================== */
        .btn {
            border-radius: 8px !important; /* Lengkungan tombol seragam */
            font-weight: 600 !important;
            padding: 0.55rem 1.25rem !important; /* Padding elegan modern */
            font-size: 0.825rem !important;
            letter-spacing: -0.01em !important;
            transition: all 0.15s ease-in-out !important;
            text-transform: none !important; /* Nonaktifkan paksaan uppercase */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
        }
        .btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 7px 14px -3px rgba(0, 0, 0, 0.12), 0 3px 6px -2px rgba(0, 0, 0, 0.08) !important;
        }
        .btn:active {
            transform: translateY(0) !important;
        }
        /* Tombol Ukuran Kecil */
        .btn.btn-sm, .btn-sm, .btn-xs, button.btn-sm {
            padding: 0.35rem 0.85rem !important;
            font-size: 0.75rem !important;
            border-radius: 6px !important;
        }
        /* Tombol Ukuran Besar */
        .btn.btn-lg, .btn-lg {
            padding: 0.75rem 1.75rem !important;
            font-size: 0.95rem !important;
            border-radius: 10px !important;
        }

        .cke_notifications_area {
            display: none;
        }

        .modal-dialog {
            box-shadow: unset !important;
        }

        /* ===================== DATATABLES ===================== */
        .dataTables_paginate.paging_simple_numbers {
            display: flex !important;
            justify-content: flex-end !important;
        }

        #DataTables_Table_0_filter.dataTables_filter label {
            display: flex;
            align-items: center;
            justify-content: end;
        }

        #DataTables_Table_0_filter.dataTables_filter label input {
            width: 200px;
        }

        .search-wrapper .input-holder {
            overflow: unset !important;
        }

        .dataTables_filter {
            display: flex;
            justify-content: flex-end;
        }

        .dataTables_filter label {
            display: flex !important;
            align-items: center;
            width: 200px;
            gap: 10px;
        }

        /* ===================== BADGE CUSTOM ===================== */
        .badge-light-blue {
            background-color: rgba(17, 205, 239, 0.2) !important;
            color: #0d9db8 !important;
        }

        .badge-light-reed {
            background-color: rgba(245, 54, 92, 0.15) !important;
            color: #f5365c !important;
        }

        .angka-warna-biru {
            background-color: rgba(94, 114, 228, 0.15) !important;
            color: #5e72e4 !important;
            float: right;
        }

        .angka-warna-merah {
            background-color: rgba(245, 54, 92, 0.15) !important;
            color: #f5365c !important;
            float: right;
        }

        .angka-warna-kuning {
            background-color: rgba(251, 99, 64, 0.15) !important;
            color: #fb6340 !important;
            float: right;
        }

        /* ===================== SELECT2 MODAL FIX ===================== */
        .modal-open .select2-dropdown {
            z-index: 10060;
        }

        .modal-open .select2-close-mask {
            z-index: 10055;
        }

.search-expandable {
    transition: width 0.3s ease;
}

.search-input {
    min-width: 230px;
    width: 230px;
    transition: width 0.3s ease;
}

.search-input.expanded,
.search-input:focus {
    width: var(--search-expanded-width, 400px);
}
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">



    <div class="min-height-300 bg-dark position-absolute w-100"></div>

    @include('layouts.sidebar')

    <main class="main-content position-relative border-radius-lg">

        @php
            $navPath       = Request::path();
            $showIndividu  = $navPath == 'transaksi/jurnal_angsuran_individu';
            $showKelompok  = $navPath == 'transaksi/jurnal_angsuran';
        @endphp

        {{-- ===================== NAVBAR ===================== --}}
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl"
             id="navbarBlur"
             data-scroll="false">
            <div class="container-fluid py-1 px-3 align-items-center">

                {{-- Hamburger (mobile) --}}
                <div class="d-xl-none me-3 d-flex align-items-center">
                    <button type="button"
                            class="btn btn-sm p-2 shadow-none border-0"
                            id="mobileSidenavToggle"
                            aria-label="Toggle sidebar"
                            title="Menu"
                            style="background: transparent; line-height: 1;">
                        <i class="fas fa-bars" style="color: #ffffff; font-size: 1.2rem;"></i>
                    </button>
                </div>

                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb" class="d-flex flex-column justify-content-center">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm">
                            <a class="opacity-5 text-white" href="/dashboard">Pages</a>
                        </li>
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                            {{ ucwords(str_replace('/', ' / ', Request::path())) }}
                        </li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">
                        {{ Session::get('nama_lembaga') }}
                    </h6>
                </nav>

                <div class="collapse navbar-collapse me-md-0 me-sm-4 align-items-center" id="navbar">

                    {{-- Search box kondisional --}}
                    @if ($showIndividu || $showKelompok)
                        <div class="pe-md-3 d-flex align-items-center">
                            <div class="input-group search-expandable">
                                @if ($showIndividu)
                                    <input id="cariAnggota"
                                           type="text"
                                           class="form-control border-start-0 ps-1 search-input"
                                           placeholder="Individu (NIK / Nama Peminjam)"
                                           autocomplete="off"
                                           style="min-width: 230px;">
                                @else
                                    <input id="cariKelompok"
                                           type="text"
                                           class="form-control border-start-0 ps-1 search-input"
                                           placeholder="Kelompok (Nama / Kode Kelompok)"
                                           autocomplete="off"
                                           style="min-width: 230px;">
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="ms-md-auto"></div>

                    {{-- Nav items kanan --}}
                    <ul class="navbar-nav justify-content-end align-items-center">

                        {{-- Avatar + Dropdown --}}
                        <li class="nav-item dropdown d-flex align-items-center ps-2">
                            <a href="javascript:;"
                               class="nav-link text-white font-weight-bold px-0 d-flex align-items-center gap-2"
                               id="userDropdown"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                @php
                                    $foto          = Session::get('foto');
                                    $jk            = auth()->user()->jk ?? Session::get('jk');
                                    $defaultAvatar = ($jk == 'P')
                                        ? asset('argon/img/female.jpg')
                                        : asset('argon/img/male.jpg');
                                    $fotoSrc       = $foto
                                        ? asset('storage/profil/' . $foto)
                                        : $defaultAvatar;
                                @endphp
                                <img width="36"
                                     height="36"
                                     class="rounded-circle border border-white border-2"
                                     src="{{ $fotoSrc }}"
                                     id="profil_avatar"
                                     onerror="this.src='{{ $defaultAvatar }}'">
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
                                    <a class="dropdown-item border-radius-md" href="javascript:;" id="btnLaporanPelunasan">
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
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item border-radius-md text-danger" href="javascript:;" id="logout">
                                        <div class="d-flex align-items-center py-1">
                                            <i class="pe-7s-next-2 me-2 fs-5"></i>
                                            <span>Logout</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Configurator toggle --}}
                        <li class="nav-item px-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white p-0">
                                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        {{-- End Navbar --}}

        {{-- ===================== HIDDEN FORMS ===================== --}}
        <form action="/pelaporan/preview"
              method="post"
              id="FormLaporanSisipan"
              target="_blank"
              style="display: none;">
            @csrf
            <input type="hidden" name="type"        id="type_sisipan"   value="pdf">
            <input type="hidden" name="tahun"       id="tahun_sisipan"  value="{{ date('Y') }}">
            <input type="hidden" name="bulan"       id="bulan_sisipan"  value="{{ date('m') }}">
            <input type="hidden" name="hari"        id="hari_sisipan"   value="{{ date('d') }}">
            <input type="hidden" name="laporan"     id="laporan"        value="pelunasan">
            <input type="hidden" name="sub_laporan" id="sub_laporan"    value="">
        </form>

        {{-- ===================== MODAL SCAN QR ===================== --}}
        <div class="modal fade"
             id="staticBackdrop"
             data-bs-backdrop="static"
             data-bs-keyboard="false"
             tabindex="-1"
             aria-labelledby="staticBackdropLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Scan Kartu Angsuran</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

        {{-- ===================== PAGE CONTENT ===================== --}}
        <div class="container-fluid py-4">
            @yield('content')
        </div>

    </main>

    {{-- ===================== ARGON CONFIGURATOR ===================== --}}
    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"></i>
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
                        <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark"           data-color="dark"    onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info"           data-color="info"    onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success"        data-color="success" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning"        data-color="warning" onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger"         data-color="danger"  onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 different sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white"    onclick="sidebarType(this)">White</button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2"             data-class="bg-default"  onclick="sidebarType(this)">Dark</button>
                </div>
                <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
                <div class="d-flex my-3">
                    <h6 class="mb-0">Navbar Fixed</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4">
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
                    </div>
                </div>
                <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/argon-dashboard">Free Download</a>
                <a class="btn btn-outline-dark w-100" href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard">View documentation</a>
                <div class="w-100 text-center">
                    <a class="github-button"
                       href="https://github.com/creativetimofficial/argon-dashboard"
                       data-icon="octicon-star"
                       data-size="large"
                       data-show-count="true"
                       aria-label="Star creativetimofficial/argon-dashboard on GitHub">Star</a>
                    <h6 class="mt-3">Thank you for sharing!</h6>
                    <a href="https://twitter.com/intent/tweet?text=Check%20Argon%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fargon-dashboard"
                       class="btn btn-dark mb-0 me-2"
                       target="_blank">
                        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/argon-dashboard"
                       class="btn btn-dark mb-0 me-2"
                       target="_blank">
                        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== HIDDEN LOGOUT FORM ===================== --}}
    <form action="/logout" method="post" id="formLogout" style="display: none;">
        @csrf
    </form>

    @yield('modal')

    {{-- ===================== CORE JS ===================== --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>
    <script src="{{ asset('argon/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('argon/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/chartjs.min.js') }}"></script>

    {{-- ===================== THIRD-PARTY JS ===================== --}}
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

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    {{-- jQuery MaskMoney --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"
            integrity="sha512-Rdk63VC+1UYzGSgd3u2iadi0joUrcwX0IWp2rTh6KXFoAmgOjRS99Vynz1lJPT8dLjvo6JZOqpAHJyfCEZ5KoA=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>

    {{-- Font Awesome JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"
            integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>

    {{-- jsTree --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>

    {{-- Bootstrap Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

  <!-- Socket.io -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.min.js"></script>

  <!-- Flatpickr -->
  <script src="/assets/js/plugins/flatpickr.min.js"></script>

  <!-- Argon Dashboard JS -->
  <script async src="/assets/js/material-dashboard.min.js?v=1716515606"></script>

    @yield('script')
    {{-- ===================== INLINE SCRIPTS ===================== --}}
    <script>
        // Auto-logout jika session invoice aktif dan bukan di halaman dashboard
        document.addEventListener('DOMContentLoaded', function () {
            if (
                !window.location.href.includes('/dashboard') &&
                {!! json_encode(Session::get('invoice') !== null) !!}
            ) {
                document.getElementById('formLogout').submit();
            }

            // ===================== SEARCH EXPAND =====================
            var searchInput = document.querySelector('.search-input');
            if (searchInput) {
                var userDropdown = document.getElementById('userDropdown');

                function calcExpandedWidth() {
                    if (!userDropdown) return 400;
                    var inputRect = searchInput.getBoundingClientRect();
                    var dropRect  = userDropdown.getBoundingClientRect();
                    var available = dropRect.left - inputRect.left - 16;
                    return Math.max(available, 230);
                }

                function expandSearch() {
                    searchInput.style.width = calcExpandedWidth() + 'px';
                }

                function collapseSearch() {
                    if (searchInput.value.trim() === '') {
                        searchInput.style.width = '230px';
                    }
                }

                searchInput.addEventListener('focus', expandSearch);
                searchInput.addEventListener('input', function () {
                    if (this.value.trim() !== '') {
                        expandSearch();
                    } else {
                        collapseSearch();
                    }
                });
                searchInput.addEventListener('blur', collapseSearch);

                window.addEventListener('resize', function () {
                    if (document.activeElement === searchInput || searchInput.value.trim() !== '') {
                        expandSearch();
                    }
                });
            }
            // ===================== END SEARCH EXPAND =====================
        });

        // Formatter angka rupiah
        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        // Pace.js config
        window.paceOptions = {
            ajax: true,
            document: false,
            eventLag: false,
            elements: {
                selectors: ['.g-sidenav-show']
            }
        };

        // Toast SweetAlert2 mixin
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

        // Toastr global
        function Toastr(icon, text) {
            var canvas  = document.createElement('canvas');
            var ctx     = canvas.getContext('2d');
            ctx.font    = '1.2rem Nimrod MT';
            var width   = Math.ceil(ctx.measureText(text).width) + 100;
            Toast.fire({ icon, title: text, width });
        }

        // MultiToast global
        function MultiToast(icon, text) {
            var canvas  = document.createElement('canvas');
            var ctx     = canvas.getContext('2d');
            ctx.font    = '1.2rem Nimrod MT';
            var width   = Math.ceil(ctx.measureText(text).width) + 100;

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
            }).fire({ icon, title: text, width });
        }

        // Buka window baru
        function open_window(link) {
            return window.open(link);
        }

        // Typeahead — cari anggota individu
        $('#cariAnggota').typeahead({
            source: function (query, process) {
                return $.get('/perguliran/cari_anggota', { query }, function (result) {
                    process(result.map(item => ({
                        id:    item.id,
                        name:  `${item.namadepan} [${item.domisi}, ${item.nama_desa}] - ${item.id} [${item.nik}]`,
                        value: item.id
                    })));
                });
            },
            afterSelect: function (item) {
                if (window.location.pathname.includes('jurnal_angsuran_individu')) {
                    $.get('/transaksi/form_angsuran_individu/' + item.id, function (result) {
                        var ch_pokok = document.getElementById('chartP').getContext('2d');
                        var ch_jasa  = document.getElementById('chartJ').getContext('2d');
                        angsuran(true, result);
                        makeChart('pokok', ch_pokok, result.sisa_pokok, result.sum_pokok);
                        makeChart('jasa',  ch_jasa,  result.sisa_jasa,  result.sum_jasa);
                        $('#loan-id').html(item.id);
                    });
                } else {
                    window.location.href = '/transaksi/jurnal_angsuran_individu?pinkel=' + item.id;
                }
            }
        });

        // Typeahead — cari kelompok
        $('#cariKelompok').typeahead({
            source: function (query, process) {
                return $.get('/perguliran/cari_kelompok', { query }, function (result) {
                    process(result.map(item => ({
                        id:    item.id,
                        name:  `${item.nama_kelompok} [${item.kd_kelompok}, ${item.nama_desa}] - Loan ID: ${item.id}`,
                        value: item.id
                    })));
                });
            },
            afterSelect: function (item) {
                if (window.location.pathname.includes('jurnal_angsuran')) {
                    $.get('/transaksi/form_angsuran/' + item.id, function (result) {
                        $('#loan-id').html(item.id);
                        $('#id').val(item.id);
                        $('#_pokok').val(result.sisa_pokok);
                        $('#_jasa').val(result.sisa_jasa);
                        $('#alokasi_pokok').html('Rp. ' + formatter.format(result.alokasi_pokok));
                        $('#alokasi_jasa').html('Rp. '  + formatter.format(result.alokasi_jasa));

                        if (typeof chartP !== 'undefined') chartP.destroy();
                        if (typeof chartJ !== 'undefined') chartJ.destroy();

                        chartP = new Chart(document.getElementById('chartP').getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Pokok', 'Sisa'],
                                datasets: [{
                                    label: 'Rp. ',
                                    data: [result.sum_pokok, result.sisa_pokok],
                                    backgroundColor: ['rgb(54, 162, 235)', 'rgb(255, 99, 132)'],
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
                                    backgroundColor: ['rgb(255, 205, 86)', 'rgb(255, 99, 132)'],
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

        // Buat chart donut
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
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Update form angsuran
        function angsuran(destroy = false, result) {
            $('#pokok').val(formatter.format(result.saldo_pokok));
            $('#jasa').val(formatter.format(result.saldo_jasa));
            $('#id').val(result.pinkel.id);
            $('#_pokok').val(result.sisa_pokok);
            $('#_jasa').val(result.sisa_jasa);

            if (destroy) {
                if (typeof chr_pokok !== 'undefined' && chr_pokok) chr_pokok.destroy();
                if (typeof chr_jasa  !== 'undefined' && chr_jasa)  chr_jasa.destroy();
            }

            $('#alokasi_pokok').html('Rp. ' + formatter.format(result.alokasi_pokok));
            $('#alokasi_jasa').html('Rp. '  + formatter.format(result.alokasi_jasa));
            $('#pokok, #jasa, #denda').trigger('change');
        }

        // Konfirmasi logout
        $('#logout').on('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Logout',
                text: 'Dengan klik tombol logout maka anda tidak bisa membuka halaman ini lagi sebelum melakukan login ulang, Logout?',
                showCancelButton: true,
                confirmButtonText: 'Logout',
                cancelButtonText: 'Batal',
                icon: 'info'
            }).then(result => {
                if (result.isConfirmed) {
                    $('#formLogout').submit();
                }
            });
        });

        // Tombol navbar — laporan & akun
        $('#btnLaporanPelunasan').on('click', function (e) {
            e.preventDefault();
            $('input#laporan').val('pelunasan');
            $('#FormLaporanSisipan').submit();
        });

        $('#btnAcount').on('click', function (e) {
            e.preventDefault();
            window.open('/profil');
        });

        $('#btnInvoiceTs').on('click', function (e) {
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
        // TinyMCE init
        tinymce.init({
            selector: '.tiny-mce-editor',
            plugins: 'table visualblocks fullscreen',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | align | table fullscreen | removeformat',
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace;',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'ARAFII'
        });

        // Smooth scrollbar (Windows only)
        if (navigator.platform.indexOf('Win') > -1 && document.querySelector('#sidenav-scrollbar')) {
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
        }
    </script>

    {{-- Session toast --}}
    @if (session('pesan'))
        <script>
            Toastr('success', "{{ session('pesan') }}")
        </script>
    @endif

    {{-- Copyright tahun otomatis --}}
    <script>
        document.querySelectorAll('.copyright-year').forEach(el => {
            el.textContent = new Date().getFullYear();
        });
    </script>

    {{-- Github buttons --}}
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    {{-- ===================== MOBILE SIDEBAR TOGGLE ===================== --}}
    <script>
        (function () {
            var toggleBtn = document.getElementById('mobileSidenavToggle');
            var sidenav   = document.getElementById('sidenav-main');
            var body      = document.body;
            var SIDENAV_W = 275;

            var overlay = document.createElement('div');
            overlay.id  = 'sidenavOverlay';
            Object.assign(overlay.style, {
                display:    'none',
                position:   'fixed',
                top:        '0',
                left:       SIDENAV_W + 'px',
                right:      '0',
                bottom:     '0',
                background: 'rgba(0,0,0,0.45)',
                zIndex:     '1048',
                cursor:     'pointer'
            });
            document.body.appendChild(overlay);

            function openSidenav() {
                body.classList.add('g-sidenav-pinned');
                overlay.style.display = 'block';
                if (sidenav) sidenav.classList.add('show-mobile');
            }

            function closeSidenav() {
                body.classList.remove('g-sidenav-pinned');
                overlay.style.display = 'none';
                if (sidenav) sidenav.classList.remove('show-mobile');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    body.classList.contains('g-sidenav-pinned') ? closeSidenav() : openSidenav();
                });
            }

            overlay.addEventListener('click', closeSidenav);

            if (sidenav) {
                sidenav.addEventListener('click', function (e) {
                    if (window.innerWidth >= 1200) return;
                    if (e.target.closest('.nav-link:not(.menu-toggle)')) closeSidenav();
                });
            }
        })();
    </script>

    {{-- ===================== ARGON DASHBOARD JS ===================== --}}
    <script src="{{ asset('argon/js/argon-dashboard.min.js') }}?v=2.1.0"></script>

    {{-- Fade-in halaman setelah selesai dimuat --}}
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
