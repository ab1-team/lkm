@extends('layouts.base')

@section('content')
<style>
    .status-active {
        color: green;
    }
    .status-inactive {
        color: red;
    }
    .status-pending {
        color: orange;
    }
    .status-default {
        color: black;
    }
</style>
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="fa fa-lock"></i>
                </div>
                <div><b>Daftar Rekening Ditutup</b>
                    <div class="page-title-subheading">
                         {{ Session::get('nama_lembaga') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-">
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush table-hover table-click" width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nomor Rekening</th>
                                    <th>Nama Anggota</th>
                                    <th>Jenis Simpanan</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Buka</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
              </div>
            </div>
        </div>
        <div class="text-sm">
            <a href="/simpanan" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar Simpanan Aktif
            </a>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        var table = $('.table').DataTable({
            language: {
                paginate: {
                    previous: "&laquo;",
                    next: "&raquo;"
                }
            },
            processing: true,
            serverSide: true,
            ajax: "/simpanan_ditutup",
            columns: [
                {
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'nomor_rekening',
                    name: 'nomor_rekening'
                },
                {
                    data: 'nama_anggota',
                    name: 'nama_anggota'
                },
                {
                    data: 'jenis_simpanan',
                    name: 'jenis_simpanan'
                },
                {
                    data: 'jumlah',
                    name: 'jumlah',
                    visible: false,
                    searchable: false
                },
                {
                    data: 'tgl_buka',
                    name: 'tgl_buka',
                    visible: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        $('.table').on('click', 'tbody tr', function(e) {
            var data = table.row(this).data();
            window.location.href = '/simpanan/' + data.id;
        });
    </script>
@endsection