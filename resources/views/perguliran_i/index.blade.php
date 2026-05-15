@extends('layouts.base')

@section('content')

    <div class="app-main__inner">
        <div class="card mb-3 border-0 shadow-sm" style="border-radius: 8px; overflow: hidden; background: #fff;">
            <div class="card-body p-1">
                <ul class="nav nav-pills nav-fill" style="margin: 0 !important;">
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-0" href="#Proposal" class="nav-link {{ $status == 'p' ? 'active' : '' }}" style="border-radius: 8px !important;">
                            <i class="fa-solid fa-file-circle-plus"></i><b>&nbsp; &nbsp;Proposal (P)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-1" href="#Verified" class="nav-link {{ $status == 'v' ? 'active' : '' }}" style="border-radius: 8px !important;">
                            <i class="fa fa-window-restore"></i><b>&nbsp; &nbsp;Verified (V)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-2" href="#Waiting" class="nav-link {{ $status == 'w' ? 'active' : '' }}" style="border-radius: 8px !important;">
                            <i class="fa-solid fa-history"></i><b>&nbsp; &nbsp;Waiting (W)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-3" href="#Aktif" class="nav-link {{ $status == 'a' ? 'active' : '' }}" style="border-radius: 8px !important;">
                            <i class="fa-solid fa-arrow-down-up-across-line"></i><b>&nbsp; &nbsp;Aktif (A)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-4" href="#Lunas" class="nav-link {{ $status == 'l' ? 'active' : '' }}" style="border-radius: 8px !important;">
                            <i class="fa-solid fa-person-circle-check"></i><b>&nbsp; &nbsp;Lunas (L)</b>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <style>
            /* Selective Nuclear CSS Reset to purge all pseudo-element hyphens and artifacts */
            .card-body::before,
            .card-body::after,
            .card-body ul.nav-pills::before,
            .card-body ul.nav-pills::after,
            .nav-pills .nav-item::before,
            .nav-pills .nav-item::after {
                content: none !important;
                display: none !important;
            }

            /* Force-Disable Dynamic Background Moving-Tab Effects */
            .card-body .nav-pills .moving-tab {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }

            .card-body ul.nav-pills {
                gap: 6px;
                list-style: none !important;
                list-style-type: none !important;
                padding-left: 0 !important;
            }
            
            .nav-pills .nav-item {
                list-style: none !important;
                list-style-type: none !important;
            }
            
            .nav-pills .nav-item .nav-link {
                cursor: pointer !important;
                font-weight: 600;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid transparent;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .nav-pills .nav-item .nav-link:not(.active):hover {
                background-color: rgba(94, 114, 228, 0.06) !important;
                color: #5e72e4 !important;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04) !important;
            }

            /* Modern Soft Tint Transparent Style for Loan ID Badges */
            .table .badge {
                box-shadow: none !important;
                font-weight: 600;
                padding: 4px 8px !important;
            }
            .table .badge.bg-primary, .table .badge.badge-light-blue, .table .badge.bg-info {
                background-color: rgba(94, 114, 228, 0.12) !important;
                color: #5e72e4 !important;
                border: 1px solid rgba(94, 114, 228, 0.2) !important;
            }
            .table .badge.bg-success {
                background-color: rgba(45, 206, 137, 0.12) !important;
                color: #2dce89 !important;
                border: 1px solid rgba(45, 206, 137, 0.2) !important;
            }
            .table .badge.bg-danger {
                background-color: rgba(245, 54, 92, 0.12) !important;
                color: #f5365c !important;
                border: 1px solid rgba(245, 54, 92, 0.2) !important;
            }
            .table .badge.bg-warning {
                background-color: rgba(251, 99, 64, 0.12) !important;
                color: #fb6340 !important;
                border: 1px solid rgba(251, 99, 64, 0.2) !important;
            }
            .table .badge.bg-secondary {
                background-color: rgba(136, 152, 170, 0.12) !important;
                color: #8898aa !important;
                border: 1px solid rgba(136, 152, 170, 0.2) !important;
            }


            @media (max-width: 576px) {
                .nav-item .nav-link {
                    display: flex;
                    justify-content: center;
                }
            }
        </style>
        

        <div class="tab-content">
            <div class="tab-pane tabs-animation fade  {{ $status == 'p' ? 'show active' : '' }}" id="Proposal" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">

                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click " width="100%" id="TbProposal">
                                        <thead>
                                            <tr>
                                                <th>Nama Anggota P</th>
                                                <th>Desa</th>
                                                <th>Nama Barang</th>
                                                <th>Tgl Pengajuan</th>
                                                <th>Pengajuan</th>
                                                <th>Jasa/Jangka</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane tabs-animation fade{{ $status == 'v' ? 'show active' : '' }}" id="Verified" role="tabpanel" >
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3 card">

                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbVerified">
                                        <thead>
                                            <tr>
                                                <th>Nama Anggota V</th>
                                                <th>Desa</th>
                                                <th>Tgl Verified</th>
                                                <th>Verifikasi</th>
                                                <th>Jasa/Jangka</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <div class="tab-pane tabs-animation fade{{ $status == 'w' ? 'show active' : '' }}" id="Waiting" role="tabpanel" >
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbWaiting">
                                        <thead>
                                            <tr>
                                                <th>Nama Anggota W</th>
                                                <th>Desa</th>
                                                <th>Tgl Waiting</th>
                                                <th>Alokasi</th>
                                                <th>Jasa/Jangka</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane tabs-animation fade{{ $status == 'a' ? 'show active' : '' }}" id="Aktif" role="tabpanel" >
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbAktif">
                                        <thead>
                                            <tr>
                                                <th>Nama Anggota A</th>
                                                <th>Desa</th>
                                                <th>Tgl Cair</th>
                                                <th>Alokasi</th>
                                                <th>Jasa/Jangka</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane tabs-animation fade{{ $status == 'l' ? 'show active' : '' }}" id="Lunas" role="tabpanel" >
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbLunas">
                                        <thead>
                                            <tr>
                                                <th>Nama Anggota L</th>
                                                <th>Alamat</th>
                                                <th>Tgl Cair</th>
                                                <th>Verifikasi</th>
                                                <th>Jasa/Jangka</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script>
        var tbProposal = CreateTable('#TbProposal', '/perguliran_i/proposal', [{
            data: 'namadepan',
            name: 'namadepan'
        }, {
            data: 'anggota.d.nama_desa',
            name: 'anggota.d.nama_desa'
        }, {
            data: 'nama_barang',
            name: 'nama_barang'
        }, {
            data: 'tgl_proposal',
            name: 'tgl_proposal'
        }, {
            data: 'proposal',
            name: 'proposal'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbVerified = CreateTable('#TbVerified', '/perguliran_i/verified', [{
            data: 'namadepan',
            name: 'namadepan'
        }, {
            data: 'anggota.d.nama_desa',
            name: 'anggota.d.nama_desa'
        }, {
            data: 'tgl_verifikasi',
            name: 'tgl_verifikasi'
        }, {
            data: 'verifikasi',
            name: 'verifikasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbWaiting = CreateTable('#TbWaiting', '/perguliran_i/waiting', [{
            data: 'namadepan',
            name: 'namadepan'
        }, {
            data: 'anggota.d.nama_desa',
            name: 'anggota.d.nama_desa'
        }, {
            data: 'tgl_tunggu',
            name: 'tgl_tunggu'
        }, {
            data: 'alokasi',
            name: 'alokasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbAktif = CreateTable('#TbAktif', '/perguliran_i/aktif', [{
            data: 'namadepan',
            name: 'namadepan'
        }, {
            data: 'anggota.d.nama_desa',
            name: 'anggota.d.nama_desa'
        }, {
            data: 'tgl_cair',
            name: 'tgl_cair'
        }, {
            data: 'alokasi',
            name: 'alokasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbLunas = CreateTable('#TbLunas', '/perguliran_i/lunas', [{
            data: 'namadepan',
            name: 'namadepan'
        }, {
            data: 'anggota.alamat',
            name: 'anggota.alamat'
        }, {
            data: 'tgl_cair',
            name: 'tgl_cair'
        }, {
            data: 'alokasi',
            name: 'alokasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        function CreateTable(tabel, url, column) {
            var table = $(tabel).DataTable({
                language: {
                    paginate: {
                        previous: "&laquo;",
                        next: "&raquo;"
                    }
                },
                processing: true,
                serverSide: true,
                ajax: url,
                columns: column,
                order: [
                    [1, 'desc']
                ]
            })

            return table
        }

        $('#TbProposal').on('click', 'tbody tr', function(e) {
            var data = tbProposal.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbVerified').on('click', 'tbody tr', function(e) {
            var data = tbVerified.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbWaiting').on('click', 'tbody tr', function(e) {
            var data = tbWaiting.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbAktif').on('click', 'tbody tr', function(e) {
            var data = tbAktif.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbLunas').on('click', 'tbody tr', function(e) {
            var data = tbLunas.row(this).data();

            window.location.href = '/lunas_i/' + data.id
        })
    </script>
@endsection
