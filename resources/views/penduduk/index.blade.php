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
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 ps-2">
    <div class="d-flex align-items-center">
        <div class="icon icon-shape bg-white shadow-sm text-center border-radius-md d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: rgba(255,255,255,0.9) !important;">
            <i class="fa fa-street-view text-primary opacity-10" style="font-size: 1.1rem;"></i>
        </div>
        <div class="d-flex flex-column justify-content-center">
            <h4 class="text-white font-weight-bolder mb-0" style="letter-spacing: 0.5px;">Data Nasabah</h4>
            <p class="text-white opacity-8 text-xs font-weight-bold mb-0 mt-1" style="letter-spacing: 0.5px; text-transform: uppercase;">{{ Session::get('nama_lembaga') }}</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if (in_array('data_penduduk.export_excel', Session::get('tombol', [])) || true) {{-- Selalu tampil seperti sebelumnya namun rapi --}}
        <button type="submit" class="btn btn-success btn-sm shadow-sm mb-0 d-flex align-items-center" id="ExportExcel">
            <i class="fas fa-file-excel me-2"></i> Export Excel
        </button>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-flush table-hover align-items-center mb-0" width="100%">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">NIK</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Lengkap</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alamat</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Telpon</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
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

<div class="row mt-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-secondary">
                (N) Belum ada pinjaman
            </span>
            @foreach ($status_pinjaman as $status)
            <span class="badge bg-{{ $status->warna_status }}">
                ({{ $status->kd_status }})
                {{ $status->nama_status }}
            </span>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('modal')
<div class="modal fade" id="EditDesa" tabindex="-1" aria-labelledby="EditDesaLabel" aria-hidden="true">
    <div class="dmodal-dialog moal-lg">


    </div>
</div>
@endsection

@section('script')
<script>
    var table = $('.table-hover').DataTable({
        language: {
            paginate: {
                previous: "&laquo;",
                next: "&raquo;"
            }
        },
        processing: true,
        serverSide: true,
        ajax: "/database/penduduk",
        columns: [{
                data: 'id',
                name: 'id',
                visible: false,
                searchable: false
            },
            {
                data: 'nik',
                name: 'nik'
            },
            {
                data: 'namadepan',
                name: 'namadepan'
            },
            {
                data: 'alamat',
                name: 'alamat'
            },
            {
                data: 'hp',
                name: 'hp'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false,
            }
        ],
        order: [
            [0, 'desc']
        ]
    });

    $('.table').on('click', 'tbody tr', function (e) {
        var data = table.row(this).data();
        window.location.href = '/database/penduduk/' + data.nik;
    });

    $(document).on('click', '#ExportExcel', function (e) {
        e.preventDefault()

        $('input#laporan').val('penduduk')
        $('input#type').val('excel')
        $('#FormLaporanSisipan').submit()
    })

</script>

@endsection
