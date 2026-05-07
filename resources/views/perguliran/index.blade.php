@extends('layouts.base')

@section('content')
    <div class="app-main__inner">
        <div class="card mb-3 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; background: #fff;">
            <div class="card-body p-1">
                <ul class="nav nav-pills nav-fill" style="margin: 0 !important;">
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-0" href="#Proposal" class="nav-link {{ $status == 'p' ? 'active' : '' }}" style="border-radius: 10px !important;">
                            <i class="fa-solid fa-file-circle-plus"></i><b>&nbsp; &nbsp;Proposal (P)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-1" href="#Verified" class="nav-link {{ $status == 'v' ? 'active' : '' }}" style="border-radius: 10px !important;">
                            <i class="fa fa-window-restore"></i><b>&nbsp; &nbsp;Verified (V)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-2" href="#Waiting" class="nav-link {{ $status == 'w' ? 'active' : '' }}" style="border-radius: 10px !important;">
                            <i class="fa-solid fa-history"></i><b>&nbsp; &nbsp;Waiting (W)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-3" href="#Aktif" class="nav-link {{ $status == 'a' ? 'active' : '' }}" style="border-radius: 10px !important;">
                            <i class="fa-solid fa-arrow-down-up-across-line"></i><b>&nbsp; &nbsp;Aktif (A)</b>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a data-bs-toggle="tab" id="tab-4" href="#Lunas" class="nav-link {{ $status == 'l' ? 'active' : '' }}" style="border-radius: 10px !important;">
                            <i class="fa-solid fa-person-circle-check"></i><b>&nbsp; &nbsp;Lunas (L)</b>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <style>
            @media (max-width: 576px) {
                .nav-item .nav-link {
                    display: flex;
                    justify-content: center;
                }
            }
        </style>

        <div class="tab-content">
            <div class="tab-pane tabs-animation fade {{ $status == 'p' ? 'show active' : '' }}" id="Proposal" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbProposal">
                                        <thead>
                                            <tr>
                                                <th>Kelompok</th>
                                                <th>Alamat</th>
                                                <th>Tgl Pengajuan</th>
                                                <th>Pengajuan</th>
                                                <th>Jasa/Jangka</th>
                                                <th>
                                                    <i class="material-icons opacity-10">people</i>
                                                </th>
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
            
            <div class="tab-pane tabs-animation fade {{ $status == 'v' ? 'show active' : '' }}" id="Verified" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbVerified">
                                        <thead>
                                            <tr>
                                                <th>Kelompok</th>
                                                <th>Alamat</th>
                                                <th>Tgl Verified</th>
                                                <th>Verifikasi</th>
                                                <th>Jasa/Jangka</th>
                                                <th>
                                                    <i class="material-icons opacity-10">people</i>
                                                </th>
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
            
            <div class="tab-pane tabs-animation fade {{ $status == 'w' ? 'show active' : '' }}" id="Waiting" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbWaiting">
                                        <thead>
                                            <tr>
                                                <th>Kelompok</th>
                                                <th>Alamat</th>
                                                <th>Tgl Waiting</th>
                                                <th>Alokasi</th>
                                                <th>Jasa/Jangka</th>
                                                <th>
                                                    <i class="material-icons opacity-10">people</i>
                                                </th>
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
            
            <div class="tab-pane tabs-animation fade {{ $status == 'a' ? 'show active' : '' }}" id="Aktif" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbAktif">
                                        <thead>
                                            <tr>
                                                <th>Kelompok</th>
                                                <th>Alamat</th>
                                                <th>Tgl Cair</th>
                                                <th>Alokasi</th>
                                                <th>Jasa/Jangka</th>
                                                <th>
                                                    <i class="material-icons opacity-10">people</i>
                                                </th>
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
            
            <div class="tab-pane tabs-animation fade {{ $status == 'l' ? 'show active' : '' }}" id="Lunas" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                                <div class="table-responsive">
                                    <table class="table table-flush table-hover table-click" width="100%" id="TbLunas">
                                        <thead>
                                            <tr>
                                                <th>Kelompok</th>
                                                <th>Alamat</th>
                                                <th>Tgl Cair</th>
                                                <th>Verifikasi</th>
                                                <th>Jasa/Jangka</th>
                                                <th>
                                                    <i class="material-icons opacity-10">people</i>
                                                </th>
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
        $(document).ready(function() {
            // Initialize DataTables
            var tbProposal = CreateTable('#TbProposal', '/perguliran/proposal', [{
                data: 'nama_kelompok',
                name: 'nama_kelompok',
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).html(cellData);
                }
            }, {
                data: 'kelompok.alamat_kelompok',
                name: 'kelompok.alamat_kelompok'
            },             {
                data: 'tgl_proposal',
                name: 'tgl_proposal',
                render: function(data, type, row) {
                    if (!data) return '';
                    var date = new Date(data);
                    var day = ('0' + date.getDate()).slice(-2);
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var year = date.getFullYear();
                    return day + '/' + month + '/' + year;
                }
            }, {
                data: 'proposal',
                name: 'proposal',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data);
                }
            }, {
                data: 'jasa',
                name: 'jasa',
                orderable: false,
                searchable: false
            }, {
                data: 'pinjaman_anggota_count',
                name: 'pinjaman_anggota_count'
            }]);

            var tbVerified = CreateTable('#TbVerified', '/perguliran/verified', [{
                data: 'nama_kelompok',
                name: 'nama_kelompok',
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).html(cellData);
                }
            }, {
                data: 'kelompok.alamat_kelompok',
                name: 'kelompok.alamat_kelompok'
            },             {
                data: 'tgl_verifikasi',
                name: 'tgl_verifikasi',
                render: function(data, type, row) {
                    if (!data) return '';
                    var date = new Date(data);
                    var day = ('0' + date.getDate()).slice(-2);
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var year = date.getFullYear();
                    return day + '/' + month + '/' + year;
                }
            }, {
                data: 'verifikasi',
                name: 'verifikasi',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data);
                }
            }, {
                data: 'jasa',
                name: 'jasa',
                orderable: false,
                searchable: false
            }, {
                data: 'pinjaman_anggota_count',
                name: 'pinjaman_anggota_count'
            }]);

            var tbWaiting = CreateTable('#TbWaiting', '/perguliran/waiting', [{
                data: 'nama_kelompok',
                name: 'nama_kelompok',
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).html(cellData);
                }
            }, {
                data: 'kelompok.alamat_kelompok',
                name: 'kelompok.alamat_kelompok'
            },             {
                data: 'tgl_tunggu',
                name: 'tgl_tunggu',
                render: function(data, type, row) {
                    if (!data) return '';
                    var date = new Date(data);
                    var day = ('0' + date.getDate()).slice(-2);
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var year = date.getFullYear();
                    return day + '/' + month + '/' + year;
                }
            }, {
                data: 'alokasi',
                name: 'alokasi',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data);
                }
            }, {
                data: 'jasa',
                name: 'jasa',
                orderable: false,
                searchable: false
            }, {
                data: 'pinjaman_anggota_count',
                name: 'pinjaman_anggota_count'
            }]);

            var tbAktif = CreateTable('#TbAktif', '/perguliran/aktif', [{
                data: 'nama_kelompok',
                name: 'nama_kelompok',
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).html(cellData);
                }
            }, {
                data: 'kelompok.alamat_kelompok',
                name: 'kelompok.alamat_kelompok'
            }, {
                data: 'tgl_cair',
                name: 'tgl_cair',
                render: function(data, type, row) {
                    if (!data) return '';
                    var date = new Date(data);
                    var day = ('0' + date.getDate()).slice(-2);
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var year = date.getFullYear();
                    return day + '/' + month + '/' + year;
                }
            }, {
                data: 'alokasi',
                name: 'alokasi',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data);
                }
            }, {
                data: 'jasa',
                name: 'jasa',
                orderable: false,
                searchable: false
            }, {
                data: 'pinjaman_anggota_count',
                name: 'pinjaman_anggota_count'
            }]);

            var tbLunas = CreateTable('#TbLunas', '/perguliran/lunas', [{
                data: 'nama_kelompok',
                name: 'nama_kelompok',
                render: function(data, type, row) {
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).html(cellData);
                }
            }, {
                data: 'kelompok.alamat_kelompok',
                name: 'kelompok.alamat_kelompok'
            }, {
                data: 'tgl_cair',
                name: 'tgl_cair',
                render: function(data, type, row) {
                    if (!data) return '';
                    var date = new Date(data);
                    var day = ('0' + date.getDate()).slice(-2);
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var year = date.getFullYear();
                    return day + '/' + month + '/' + year;
                }
            }, {
                data: 'alokasi',
                name: 'alokasi',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID').format(data);
                }
            }, {
                data: 'jasa',
                name: 'jasa',
                orderable: false,
                searchable: false
            }, {
                data: 'pinjaman_anggota_count',
                name: 'pinjaman_anggota_count'
            }]);

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
                        [2, 'desc']
                    ]
                });

                return table;
            }

            $('#TbProposal').on('click', 'tbody tr', function(e) {
                var data = tbProposal.row(this).data();
                window.location.href = '/detail/' + data.id;
            });

            $('#TbVerified').on('click', 'tbody tr', function(e) {
                var data = tbVerified.row(this).data();
                window.location.href = '/detail/' + data.id;
            });

            $('#TbWaiting').on('click', 'tbody tr', function(e) {
                var data = tbWaiting.row(this).data();
                window.location.href = '/detail/' + data.id;
            });

            $('#TbAktif').on('click', 'tbody tr', function(e) {
                var data = tbAktif.row(this).data();
                window.location.href = '/detail/' + data.id;
            });

            $('#TbLunas').on('click', 'tbody tr', function(e) {
                var data = tbLunas.row(this).data();
                window.location.href = '/lunas/' + data.id;
            });
        });
    </script>
@endsection
