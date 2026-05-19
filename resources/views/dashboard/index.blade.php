@extends('layouts.base')

@section('content')
    <form action="" method="post" id="defaultForm">
        @csrf

        <input type="hidden" name="tgl" id="tgl" value="{{ date('d/m/Y') }}">
    </form>
    <!-- Trigger Button (Hidden) -->
    @if ($jumlah_unpaid > 0)
        <button type="button" id="triggerPopup" class="d-none" data-bs-toggle="modal"
            data-bs-target="#notificationPopup"></button>
    @endif

    <style>
        .text-justify {
            text-align: justify;
        }

        .widget-content.card {
            height: 95px !important;
            min-height: 95px !important;
            max-height: 95px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-bottom: 1rem !important;
        }

        .widget-content-outer,
        .widget-content-wrapper {
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
        }
    </style>
    <div class="app-main__inner">

        <div class="app-page-title">
            <div class="row">
                @if ($jumlah_saldo <= $jumlah)
                    <div class="col-12">
                        <div class="alert alert-warning text-black" role="alert">
                            Sepertinya saldo transaksi anda belum tersimpan di aplikasi. Silahkan Klik <a href="#"
                                data-href="/simpan_saldo?bulan=00&tahun={{ date('Y') }}" class="alert-link"
                                id="simpanSaldo">Disini</a> untuk menyimpan
                            saldo transaksi anda
                        </div>
                @endif
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper" id="btnAktif">
                                <div class="widget-content-left">
                                    <div class="widget-heading"
                                        style="display: flex; justify-content: space-between; align-items: right; font-size: 11px;">
                                        <span>
                                            <h6><b>Pinjaman Aktif</b></h6>
                                        </span>
                                        <div class="dropdown text-end">
                                            <span class="text-xs text-secondary">&nbsp;&nbsp; {{ date('d/m/y') }}</span>
                                        </div>
                                    </div> <br>
                                    <div class="widget-subheading">
                                        <span class="text-sm text-end text-success font-weight-bolder mt-auto mb-0">
                                            &nbsp;<span class="font-weight-normal text-secondary">&nbsp;</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-success" style="font-size: 17px;">
                                        {{ $pinjaman_anggota }} Nasabah
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper" id="btnpinjaman">
                                <div class="widget-content-left">
                                    <div class="widget-heading"
                                        style="display: flex; justify-content: space-between; align-items: right; font-size: 11px;">
                                        <span>
                                            <h6><b>Permohonan Kredit</b></h6>
                                        </span>
                                    </div> <br>
                                    <div class="widget-subheading">
                                        <div class="d-flex gap-2 mt-1">
                                            <span class="badge bg-info px-2 py-1" style="font-size:12px;">
                                                {{ $proposal }} P
                                            </span>
                                            <span class="badge bg-danger px-2 py-1" style="font-size:12px;">
                                                {{ $verifikasi }} V
                                            </span>
                                            <span class="badge bg-warning px-2 py-1" style="font-size:12px;">
                                                {{ $waiting }} W
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-warning" style="font-size: 17px;">
                                        {{ $proposal + $verifikasi + $waiting }} File
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper" id="btnjatuhTempo">
                                <div class="widget-content-left">
                                    <div class="widget-heading"
                                        style="display: flex; justify-content: space-between; align-items: right; font-size: 11px;">
                                        <span>
                                            <h6><b>Jatuh tempo</b></h6>
                                        </span>
                                        <div class="dropdown text-end">
                                            <span class="text-xs text-danger">&nbsp;&nbsp; &#33; tagihan</span>
                                        </div>
                                    </div> <br>
                                    <div class="widget-subheading">
                                        <span class="font-weight-bolder text-success" id="nunggak">
                                            <div class="spinner-border xs text-info" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span> menunggak
                                    </div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-danger" style="font-size: 17px;">
                                        <span id="jatuh_tempo">
                                            <div class="spinner-border sm text-info" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span> Hari Ini
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <br>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="main-card card w-100">
                        <div class="card-body p-3 pb-2">
                            <h5 class="card-title mb-1">Angsuran Hari Ini</h5>
                            <div class="mb-3" style="height: 180px; min-height: 180px; position: relative;">
                                <canvas id="myChart"></canvas>
                            </div>
                            <div class="pt-3 border-top">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                    <div class="text-xs">
                                        Total Ang.Pokok: <b>Rp. <span id="total_angsur_pokok">0.00</span></b>
                                    </div>
                                    <div class="text-xs">
                                        Total Ang.Jasa: <b>Rp. <span id="total_angsur_jasa">0.00</span></b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-8 mb-4">
                    <div class="card w-100">
                        <div class="card-header pb-0 p-4 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-4">Realisasi Pendapatan dan Beban</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-md badge-dot me-4">
                                    <i class="bg-success"></i>
                                    <span class="text-dark text-xs">Pendapatan</span>
                                </span>
                                <span class="badge badge-md badge-dot me-4">
                                    <i class="bg-warning"></i>
                                    <span class="text-dark text-xs">Beban</span>
                                </span>
                                <span class="badge badge-md badge-dot">
                                    <i class="bg-info"></i>
                                    <span class="text-dark text-xs">Laba</span>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart w-100">
                                <canvas id="chart-line" class="chart-canvas" height="180"></canvas>
                            </div>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @php
        $p = $saldo[4];
        $b = $saldo[5];
        $surplus = $saldo['surplus'];
    @endphp

    <textarea name="msgInvoice" id="msgInvoice" class="d-none">{{ Session::get('msg') }}</textarea>

    <form action="/pelaporan/preview" method="post" id="FormLaporanDashboard" target="_blank">
        @csrf

        <input type="hidden" name="type" id="type" value="pdf">
        <input type="hidden" name="tahun" id="tahun" value="{{ date('Y') }}">
        <input type="hidden" name="bulan" id="bulan" value="{{ date('m') }}">
        <input type="hidden" name="hari" id="hari" value="{{ date('d') }}">
        <input type="hidden" name="laporan" id="laporan" value="">
        <input type="hidden" name="sub_laporan" id="sub_laporan" value="">
    </form>
@endsection

@section('modal')
    <!-- Popup Modal -->
    <div class="modal fade" id="notificationPopup" tabindex="-1" aria-labelledby="notificationPopupLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationPopupLabel">Notification</h5>
                </div>
                <div class="modal-body text-justify">
                    LKM <strong>{{ $nama_lkm }} </strong> saat ini memiliki tagihan invoice sebesar
                    <strong>Rp. {{ number_format($jumlah_invoice, 2) }}</strong> untuk perpanjangan lisensi.
                    Mohon
                    segera lakukan pembayaran untuk menghindari kemungkinan pemblokiran dari sistem.
                    <br>Pembayaran Transfer Via : <br>
                    - Bank Mandiri Nomor Rekening : <b> 185-000-417-4733 an. Santoso,</b> <br>
                    - Bank BRI Nomor Rekening : <b>0048-01-057317-505 an.Santoso</b><br>
                    Cek info selengkapnya pada menu <strong> Biaya Perpanjangan </strong> di menu pengaturan ->invoice.
                </div>
                <div class="modal-footer">
                    <button type="button" tabindex="0" class="btn btn-primary" id="logout">OK</button>
                </div>

                <form action="/logout" method="post" id="formLogout">
                    @csrf
                </form>

            </div>
        </div>
    </div>

    {{-- Modal Jatuh Dempo --}}
    <div class="modal fade" id="jatuhTempo" aria-labelledby="jatuhTempoLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="jatuhTempoLabel">Jatuh Tempo</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="nav-wrapper position-relative end-0">
                        <div class="d-flex justify-content-between p-1" role="tablist">
                            <button class="btn btn-outline-danger flex-fill me-1 active" data-bs-toggle="tab"
                                data-bs-target="#tagihan_hari_ini" role="tab" aria-controls="tagihan_hari_ini"
                                aria-selected="true">
                                Hari ini
                            </button>
                            <button class="btn btn-outline-warning flex-fill me-1" data-bs-toggle="tab"
                                data-bs-target="#menunggak" role="tab" aria-controls="menunggak"
                                aria-selected="false">
                                Menunggak
                            </button>
                            <button class="btn btn-outline-info flex-fill" data-bs-toggle="tab" data-bs-target="#tagihan"
                                role="tab" aria-controls="tagihan" aria-selected="false">
                                Tagihan
                            </button>
                        </div>

                        <div class="tab-content mt-2">

                            {{-- Hari Ini --}}
                            <div class="tab-pane fade show active" id="tagihan_hari_ini" role="tabpanel"
                                aria-labelledby="tagihan_hari_ini">
                                <div class="d-flex p-1 gap-1" role="tablist">
                                    <button class="btn btn-sm btn-outline-success active" data-bs-toggle="tab"
                                        data-bs-target="#hari_ini_individu" role="tab">
                                        Individu
                                    </button>
                                    @if(Session::get('lokasi') == 95)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tab"
                                        data-bs-target="#hari_ini_kelompok" role="tab">
                                        Kelompok
                                    </button>
                                    @endif
                                </div>
                                <div class="tab-content mt-1">
                                    <div class="tab-pane fade show active" id="hari_ini_individu" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load ID</td>
                                                                <td align="center">Nama Pemohon</td>
                                                                <td align="center">Tanggal Cair</td>
                                                                <td align="center">Tunggakan Pokok</td>
                                                                <td align="center">Tunggakan Jasa</td>
                                                                <td align="center">Jumlah</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="TbHariIni"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(Session::get('lokasi') == 95)
                                    <div class="tab-pane fade" id="hari_ini_kelompok" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load ID</td>
                                                                <td align="center">Nama Kelompok</td>
                                                                <td align="center">Tanggal Cair</td>
                                                                <td align="center">Tunggakan Pokok</td>
                                                                <td align="center">Tunggakan Jasa</td>
                                                                <td align="center">Jumlah</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="TbHariIniKelompok"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Menunggak --}}
                            <div class="tab-pane fade" id="menunggak" role="tabpanel" aria-labelledby="menunggak">
                                <div class="d-flex p-1 gap-1" role="tablist">
                                    <button class="btn btn-sm btn-outline-success active" data-bs-toggle="tab"
                                        data-bs-target="#nunggak_individu" role="tab">
                                        Individu
                                    </button>
                                    @if(Session::get('lokasi') == 95)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tab"
                                        data-bs-target="#nunggak_kelompok" role="tab">
                                        Kelompok
                                    </button>
                                    @endif
                                </div>
                                <div class="tab-content mt-1">
                                    <div class="tab-pane fade show active" id="nunggak_individu" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <form action="/perguliran_i/dokumen?status=A" target="_blank" method="post"
                                                    id="formCetakTagihan">
                                                    @csrf
                                                    <input type="hidden" name="id" id="tagihan_id">
                                                    <input type="hidden" name="report" value="suratTagihan#pdf">
                                                </form>
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Tanggal Cair</td>
                                                                <td align="center">Nama Pemohon</td>
                                                                <td align="center">Desa</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Tunggakan Pokok</td>
                                                                <td align="center">Tunggakan Jasa</td>
                                                                <td align="center">Jumlah</td>
                                                                <td align="center">Keterangan</td>
                                                                <th align="center">Tagihan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="TbMenunggak"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(Session::get('lokasi') == 95)
                                    <div class="tab-pane fade" id="nunggak_kelompok" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Tanggal Cair</td>
                                                                <td align="center">Nama Kelompok</td>
                                                                <td align="center">Desa</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Tunggakan Pokok</td>
                                                                <td align="center">Tunggakan Jasa</td>
                                                                <td align="center">Jumlah</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="TbMenunggakKelompok"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tagihan" role="tabpanel" aria-labelledby="tagihan">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <form action="/dashboard/tagihan" method="post" id="formTagihanWa">
                                            @csrf

                                            <div class="alert alert-info text-black">
                                                Kirim Whatsapp Tagihan berdasarkan tanggal jatuh tempo. Pastikan nomor HP
                                                Nasabah dapat menerima pesan Whatsapp.
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-5">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label font-weight-bolder text-xs mb-1">TGL JATUH
                                                            TEMPO</label>
                                                        <input autocomplete="off" type="text" name="tgl_tagihan"
                                                            id="tgl_tagihan" class="form-control date pesan border p-2"
                                                            style="height: 40px;" value="{{ date('d/m/Y') }}">
                                                        <small class="text-danger" id="msg_tgl_tagihan"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label font-weight-bolder text-xs mb-1">TGL
                                                            PEMBAYARAN</label>
                                                        <input autocomplete="off" type="text" name="tgl_pembayaran"
                                                            id="tgl_pembayaran" class="form-control date pesan border p-2"
                                                            style="height: 40px;" value="{{ date('d/m/Y') }}">
                                                        <small class="text-danger" id="msg_tgl_pembayaran"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label mb-1">&nbsp;</label>
                                                        <button type="submit" class="btn btn-dark w-100 mb-0"
                                                            id="PreviewTagihan" style="height: 40px;">Preview</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <textarea class="form-control d-none" name="pesan_whatsapp" id="pesan_whatsapp"></textarea>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm me-2 btn-pelaporan">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pinjaman --}}
    <div class="modal fade" id="pinjaman" aria-labelledby="pinjamanLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="pinjamanLabel">Pinjaman</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="nav-wrapper position-relative end-0">
                        <div class="d-flex justify-content-between p-1" role="tablist">
                            <button class="btn btn-outline-info flex-fill me-1 active" data-bs-toggle="tab"
                                data-bs-target="#proposal" role="tab" aria-controls="proposal" aria-selected="true">
                                Proposal
                            </button>
                            <button class="btn btn-outline-danger flex-fill me-1" data-bs-toggle="tab"
                                data-bs-target="#verifikasi" role="tab" aria-controls="verifikasi"
                                aria-selected="false">
                                Verifikasi
                            </button>
                            <button class="btn btn-outline-warning flex-fill" data-bs-toggle="tab"
                                data-bs-target="#waiting" role="tab" aria-controls="waiting" aria-selected="false">
                                Waiting
                            </button>
                        </div>

                        <div class="tab-content mt-2">

                            {{-- Proposal --}}
                            <div class="tab-pane fade show active" id="proposal" role="tabpanel" aria-labelledby="proposal">
                                <div class="d-flex p-1 gap-1" role="tablist">
                                    <button class="btn btn-sm btn-outline-success active" data-bs-toggle="tab"
                                        data-bs-target="#proposal_individu" role="tab">Individu</button>
                                    @if(Session::get('lokasi') == 95)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tab"
                                        data-bs-target="#proposal_kelompok" role="tab">Kelompok</button>
                                    @endif
                                </div>
                                <div class="tab-content mt-1">
                                    <div class="tab-pane fade show active" id="proposal_individu" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load id</td>
                                                                <td align="center">Tanggal Proposal</td>
                                                                <td align="center">Nama Pemohon P</td>
                                                                <td align="center">Jenis</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Nama Barang</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbProposal"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(Session::get('lokasi') == 95)
                                    <div class="tab-pane fade" id="proposal_kelompok" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load id</td>
                                                                <td align="center">Tanggal Proposal</td>
                                                                <td align="center">Nama Kelompok</td>
                                                                <td align="center">Jenis</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Nama Barang</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbProposalKelompok"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Verifikasi --}}
                            <div class="tab-pane fade" id="verifikasi" role="tabpanel" aria-labelledby="verifikasi">
                                <div class="d-flex p-1 gap-1" role="tablist">
                                    <button class="btn btn-sm btn-outline-success active" data-bs-toggle="tab"
                                        data-bs-target="#verifikasi_individu" role="tab">Individu</button>
                                    @if(Session::get('lokasi') == 95)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tab"
                                        data-bs-target="#verifikasi_kelompok" role="tab">Kelompok</button>
                                    @endif
                                </div>
                                <div class="tab-content mt-1">
                                    <div class="tab-pane fade show active" id="verifikasi_individu" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load id</td>
                                                                <td align="center">Tanggal Verifikasi</td>
                                                                <td align="center">Nama Pemohon V</td>
                                                                <td align="center">Jenis</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Nama Barang</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbVerifikasi"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(Session::get('lokasi') == 95)
                                    <div class="tab-pane fade" id="verifikasi_kelompok" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load id</td>
                                                                <td align="center">Tanggal Verifikasi</td>
                                                                <td align="center">Nama Kelompok</td>
                                                                <td align="center">Jenis</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Nama Barang</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbVerifikasiKelompok"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Waiting --}}
                            <div class="tab-pane fade" id="waiting" role="tabpanel" aria-labelledby="waiting">
                                <div class="d-flex p-1 gap-1" role="tablist">
                                    <button class="btn btn-sm btn-outline-success active" data-bs-toggle="tab"
                                        data-bs-target="#waiting_individu" role="tab">Individu</button>
                                    @if(Session::get('lokasi') == 95)
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tab"
                                        data-bs-target="#waiting_kelompok" role="tab">Kelompok</button>
                                    @endif
                                </div>
                                <div class="tab-content mt-1">
                                    <div class="tab-pane fade show active" id="waiting_individu" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load id</td>
                                                                <td align="center">Tanggal Tunggu</td>
                                                                <td align="center">Nama Pemohon W</td>
                                                                <td align="center">Jenis</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Nama Barang</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbWaiting"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(Session::get('lokasi') == 95)
                                    <div class="tab-pane fade" id="waiting_kelompok" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped midle" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <td align="center">No</td>
                                                                <td align="center">Load id</td>
                                                                <td align="center">Tanggal Tunggu</td>
                                                                <td align="center">Nama Kelompok</td>
                                                                <td align="center">Jenis</td>
                                                                <td align="center">Alokasi</td>
                                                                <td align="center">Nama Barang</td>
                                                                <td align="center">Keterangan</td>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbWaitingKelompok"></tbody>
                                                    </table>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm me-2 btn-pelaporan">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Pengguna Aktif --}}
    <div class="modal fade" id="aktif" aria-labelledby="aktifLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="aktifLabel">Pinjaman Aktif</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="nav-wrapper position-relative end-0">
                        {{-- Tab nav --}}
                        <div class="d-flex justify-content-between p-1" role="tablist">
                            <button class="btn btn-outline-success flex-fill @if(Session::get('lokasi') == 95) me-1 @endif active"
                                data-bs-toggle="tab" data-bs-target="#tab_individu_aktif"
                                role="tab" aria-controls="tab_individu_aktif" aria-selected="true"
                                id="btn_tab_individu">
                                Individu
                            </button>
                            @if(Session::get('lokasi') == 95)
                            <button class="btn btn-outline-primary flex-fill"
                                data-bs-toggle="tab" data-bs-target="#tab_kelompok_aktif"
                                role="tab" aria-controls="tab_kelompok_aktif" aria-selected="false"
                                id="btn_tab_kelompok">
                                Kelompok
                            </button>
                            @endif
                        </div>

                        {{-- Tab content --}}
                        <div class="tab-content mt-2">

                            {{-- Tab Individu --}}
                            <div class="tab-pane fade show active" id="tab_individu_aktif"
                                role="tabpanel" aria-labelledby="btn_tab_individu">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">NIK</td>
                                                        <td align="center">Nama Nasabah</td>
                                                        <td align="center">Alamat</td>
                                                        <td align="center">T/S</td>
                                                        <td align="center">Tanggal Cair</td>
                                                        <td align="center">Alokasi</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbAnggota"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab Kelompok (hanya lokasi 1) --}}
                            @if(Session::get('lokasi') == 95)
                            <div class="tab-pane fade" id="tab_kelompok_aktif"
                                role="tabpanel" aria-labelledby="btn_tab_kelompok">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped midle" width="100%">
                                                <thead>
                                                    <tr>
                                                        <td align="center">No</td>
                                                        <td align="center">Kode Kelompok</td>
                                                        <td align="center">Nama Kelompok</td>
                                                        <td align="center">Alamat</td>
                                                        <td align="center">T/S</td>
                                                        <td align="center">Tanggal Cair</td>
                                                        <td align="center">Alokasi</td>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbKelompok"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn-sm me-2 btn-pelaporan">
                        Print
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Angsuran --}}
    <div class="modal fade" id="detailAngsuran" aria-labelledby="detailAngsuranLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="detailAngsuranLabel">Pinjaman detailAngsuran</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tagihan --}}
    <div class="modal fade" id="tagihanPinjaman" aria-labelledby="tagihanPinjamanLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="tagihanPinjamanLabel">Tagihan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="TbTagihan"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="KirimPesan">Kirim Pesan</button>
                    <button type="button" class="btn btn-danger btn-sm" id="closeTagihan">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $(".date").flatpickr({
                dateFormat: "d/m/Y"
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const triggerButton = document.getElementById('triggerPopup');
            if (triggerButton) {
                triggerButton.click();
            }
            // Ensure modal can be interacted with correctly
            const modalElement = document.getElementById('notificationPopup');
            modalElement.addEventListener('hidden.bs.modal', function() {
                // Ensure the backdrop is removed when modal is closed
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });
        });
    </script>

    <script>
        $.ajax({
            type: 'post',
            url: '/dashboard/jatuh_tempo',
            data: $('#defaultForm').serialize(),
            success: function(result) {
                if (result.success) {
                    $('#jatuh_tempo').html(result.jatuh_tempo)

                    if (result.jatuh_tempo != '00') {
                        $('#TbHariIni').html(result.hari_ini)
                    }
                }
            }
        })

        @if(Session::get('lokasi') == 95)
        $.ajax({
            type: 'post',
            url: '/dashboard/jatuh_tempo',
            data: $('#defaultForm').serialize() + '&tipe=kelompok',
            success: function(result) {
                if (result.success) {
                    $('#TbHariIniKelompok').html(result.hari_ini)
                }
            }
        })
        @endif

        $.ajax({
            type: 'post',
            url: '/dashboard/nunggak',
            data: $('#defaultForm').serialize(),
            success: function(result) {
                if (result.success) {
                    $('#nunggak').html(result.nunggak)

                    if (result.nunggak != '00') {
                        $('#TbMenunggak').html(result.table)
                    }
                }
            }
        })

        @if(Session::get('lokasi') == 95)
        $.ajax({
            type: 'post',
            url: '/dashboard/nunggak',
            data: $('#defaultForm').serialize() + '&tipe=kelompok',
            success: function(result) {
                if (result.success) {
                    $('#TbMenunggakKelompok').html(result.table)
                }
            }
        })
        @endif

        function tagihan() {
            var tgl_tagihan = $('#tgl_tagihan').val()

            $.ajax({
                url: '/dashboard/tagihan',
                type: 'post',
                data: $('#formTagihanWa').serialize(),
                success: function(result) {
                    if (result.success) {
                        $('#TbTagihan').html(result.tagihan)
                        $('#tagihanPinjaman').modal('show')
                    }
                }
            })
        }

        $(document).on('click', '#PreviewTagihan', function(e) {
            e.preventDefault()

            tagihan()
        })

        $(document).on('click', '#closeTagihan', function() {
            $('#tagihanPinjaman').modal('hide')
            $('#jatuhTempo').modal('show')
        })

        $(document).on('click', '.btn-wa-nunggak', function(e) {
            e.preventDefault()
            var id = $(this).attr('data-id')

            $.ajax({
                url: '/dashboard/tagihan',
                type: 'post',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function(result) {
                    if (result.success) {
                        $('#TbTagihan').html(result.tagihan)
                        $('#jatuhTempo').modal('hide')
                        $('#tagihanPinjaman').modal('show')
                    }
                }
            })
        })

        $.get('/dashboard/pinjaman?status=P', function(result) {
            if (result.success) {
                $('#tbProposal').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=V', function(result) {
            if (result.success) {
                $('#tbVerifikasi').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=W', function(result) {
            if (result.success) {
                $('#tbWaiting').html(result.table)
            }
        })

        @if(Session::get('lokasi') == 95)
        $.get('/dashboard/pinjaman?status=P&tipe=kelompok', function(result) {
            if (result.success) {
                $('#tbProposalKelompok').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=V&tipe=kelompok', function(result) {
            if (result.success) {
                $('#tbVerifikasiKelompok').html(result.table)
            }
        })

        $.get('/dashboard/pinjaman?status=W&tipe=kelompok', function(result) {
            if (result.success) {
                $('#tbWaitingKelompok').html(result.table)
            }
        })
        @endif

        $.get('/dashboard/pemanfaat?status=A', function(result) {
            if (result.success) {
                $('#tbAnggota').html(result.table)
            }
        })

        @if(Session::get('lokasi') == 95)
        $.get('/dashboard/pemanfaat?status=A&tipe=kelompok', function(result) {
            if (result.success) {
                $('#tbKelompok').html(result.table)
            }
        })
        @endif

        $(document).on('click', '#KirimPesan', function(e) {
            e.preventDefault()

            var messages = [];
            $('[data-input=checked]:checked').each(function() {
                var pesan = this.value
                var number = pesan.split('||')[0]
                var anggota = pesan.split('||')[1]
                var msg = pesan.split('||')[2]

                if (!number.startsWith('08') && !number.startsWith('628')) {
                    number = '0' + number;
                }

                messages.push({
                    to: number,
                    message: msg
                })

                // Registrasi agar notifikasi muncul (Filter Sesi LKM)
                if (typeof registerLkmMessage === 'function') {
                    registerLkmMessage(number);
                }
            });

            if (messages.length == 0) return;

            $.ajax({
                type: 'POST',
                url: '{{ $api }}/api/send/personalized',
                headers: {
                    'x-api-key': '{{ $api_key }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    device_id: "{{ $wa_device_id }}",
                    messages: messages
                }),
                success: function(result) {
                    if (result.success) {
                        Swal.fire('Berhasil', 'Pesan Berhasil Masuk Antrean', 'success')
                    }
                }
            })
        })

        $(document).on('click', '#btnjatuhTempo', function(e) {
            e.preventDefault()

            $('#jatuhTempo').modal('show')


            let tab = $('#jatuhTempo').find('div.nav-wrapper div.d-flex button.active')
            if (tab.length > 0) {
                if (tab.attr('aria-controls') != 'tagihan') {
                    $('.btn-pelaporan').show()
                    setLaporan('5', tab.attr('aria-controls'))
                } else {
                    $('.btn-pelaporan').hide()
                }
            }
        })

        $(document).on('click', '#btnpinjaman', function(e) {
            e.preventDefault()

            $('#pinjaman').modal('show')
            $('.btn-pelaporan').show()

            let tab = $('#pinjaman').find('div.nav-wrapper div.d-flex button.active')
            if (tab.length > 0) {
                setLaporan('5', tab.attr('aria-controls'))
            }
        })

        $(document).on('click', '#btnAktif', function(e) {
            e.preventDefault()

            $('#aktif').modal('show')
            $('.btn-pelaporan').show()

            setLaporan('5', $('#aktif').attr('aria-controls'))
        })

        $(document).on('click', '.btn-cetak-tagihan', function(e) {
            e.preventDefault()

            var id = $(this).attr('data-id')
            $('#tagihan_id').val(id)

            $('#formCetakTagihan').submit()
        })

        $(document).on('click', 'div.nav-wrapper div.d-flex button', function() {
            var a = $(this)

            if (a.attr('aria-controls') == 'tagihan') {
                $('.btn-pelaporan').hide()
            } else {
                $('.btn-pelaporan').show()
            }
            setLaporan('5', a.attr('aria-controls'))
        })

        $(document).on('click', '.btn-pelaporan', function(e) {
            $('#FormLaporanDashboard').submit()
        })

        function setLaporan(laporan, subLaporan = null) {
            $('#FormLaporanDashboard #laporan').val(laporan);
            $('#FormLaporanDashboard #sub_laporan').val(subLaporan);
        }
    </script>

    @if (Session::get('invoice'))
        <script>
            function msgInvoice(number, msg) {
                $.ajax({
                    type: 'POST',
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
                        // Success handling without retry
                    },
                    error: function(result) {
                        // Error handling without retry
                    }
                })
            }

            msgInvoice("{{ Session::get('hp_dir') }}", $('#msgInvoice').val())
            setTimeout(() => {
                msgInvoice('0882006644656', $('#msgInvoice').val())
            }, 1000);
        </script>
    @endif
    <script>
        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })

        var ctx1 = document.getElementById("chart-line").getContext("2d");
        var ctx2 = document.getElementById("myChart").getContext("2d");

        // Argon color palette
        // Primary #5e72e4 | Success #2dce89 | Warning #fb6340 | Info #11cdef | Danger #f5365c

        // Gradient Pendapatan — Argon Success (#2dce89)
        var gradientPendapatan = ctx1.createLinearGradient(0, 230, 0, 50);
        gradientPendapatan.addColorStop(1, 'rgba(45, 206, 137, 0.3)');
        gradientPendapatan.addColorStop(0.2, 'rgba(45, 206, 137, 0.0)');
        gradientPendapatan.addColorStop(0, 'rgba(45, 206, 137, 0)');

        // Gradient Beban — Argon Warning (#fb6340)
        var gradientBeban = ctx1.createLinearGradient(0, 230, 0, 50);
        gradientBeban.addColorStop(1, 'rgba(251, 99, 64, 0.3)');
        gradientBeban.addColorStop(0.2, 'rgba(251, 99, 64, 0.0)');
        gradientBeban.addColorStop(0, 'rgba(251, 99, 64, 0)');

        // Gradient Laba — Argon Primary (#5e72e4)
        var gradientLaba = ctx1.createLinearGradient(0, 230, 0, 50);
        gradientLaba.addColorStop(1, 'rgba(94, 114, 228, 0.3)');
        gradientLaba.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
        gradientLaba.addColorStop(0, 'rgba(94, 114, 228, 0)');

        // Line chart — Argon style dengan data real
        new Chart(ctx1, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                datasets: [{
                        label: "Pendapatan",
                        tension: 0,
                        pointRadius: 0,
                        pointBackgroundColor: "#2dce89",
                        pointBorderColor: "transparent",
                        borderColor: "#2dce89",
                        borderWidth: 3,
                        backgroundColor: gradientPendapatan,
                        fill: true,
                        data: [
                            "{{ $p['1'] }}", "{{ $p['2'] }}", "{{ $p['3'] }}",
                            "{{ $p['4'] }}",
                            "{{ $p['5'] }}", "{{ $p['6'] }}", "{{ $p['7'] }}",
                            "{{ $p['8'] }}",
                            "{{ $p['9'] }}", "{{ $p['10'] }}", "{{ $p['11'] }}",
                            "{{ $p['12'] }}"
                        ],
                        maxBarThickness: 6
                    },
                    {
                        label: "Beban",
                        tension: 0,
                        pointRadius: 0,
                        pointBackgroundColor: "#fb6340",
                        pointBorderColor: "transparent",
                        borderColor: "#fb6340",
                        borderWidth: 3,
                        backgroundColor: gradientBeban,
                        fill: true,
                        data: [
                            "{{ $b['1'] }}", "{{ $b['2'] }}", "{{ $b['3'] }}",
                            "{{ $b['4'] }}",
                            "{{ $b['5'] }}", "{{ $b['6'] }}", "{{ $b['7'] }}",
                            "{{ $b['8'] }}",
                            "{{ $b['9'] }}", "{{ $b['10'] }}", "{{ $b['11'] }}",
                            "{{ $b['12'] }}"
                        ],
                        maxBarThickness: 6
                    },
                    {
                        label: "Laba",
                        tension: 0,
                        pointRadius: 0,
                        pointBackgroundColor: "#5e72e4",
                        pointBorderColor: "transparent",
                        borderColor: "#5e72e4",
                        borderWidth: 3,
                        backgroundColor: gradientLaba,
                        fill: true,
                        data: [
                            "{{ $surplus['1'] }}", "{{ $surplus['2'] }}", "{{ $surplus['3'] }}",
                            "{{ $surplus['4'] }}",
                            "{{ $surplus['5'] }}", "{{ $surplus['6'] }}", "{{ $surplus['7'] }}",
                            "{{ $surplus['8'] }}",
                            "{{ $surplus['9'] }}", "{{ $surplus['10'] }}", "{{ $surplus['11'] }}",
                            "{{ $surplus['12'] }}"
                        ],
                        maxBarThickness: 6
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#9ca2b7',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: true,
                            color: '#9ca2b7',
                            padding: 10,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        // Palette warna Argon — otomatis cycling
        var argonColors = [
            '#5e72e4', '#2dce89', '#11cdef', '#fb6340', '#f5365c',
            '#825ee4', '#2dcecc', '#8392ab', '#ffd600', '#f4a423',
        ];

        // Data dari backend — dinamis sesuai jenis produk di DB
        var angsuranData = @json($angsuran);

        var pieLabels = [];
        var piePokok = [];
        var pieJasa = [];
        var pieBgColors = [];
        var total_pokok = 0;
        var total_jasa = 0;
        var colorIdx = 0;

        Object.values(angsuranData).forEach(function(jp) {
            pieLabels.push(jp.label);
            piePokok.push(jp.pokok);
            pieJasa.push(jp.jasa);
            // Gunakan warna_ipp dari DB (argon class), atau fallback ke palette
            var warna = jp.warna;
            var warnaMap = {
                'primary': '#5e72e4',
                'success': '#2dce89',
                'info': '#11cdef',
                'warning': '#fb6340',
                'danger': '#f5365c',
                'default': '#344767',
                'secondary': '#8392ab',
                'dark': '#212529',
                'light': '#ced4da'
            };
            pieBgColors.push(warnaMap[warna] || argonColors[colorIdx % argonColors.length]);
            total_pokok += parseFloat(jp.pokok) || 0;
            total_jasa += parseFloat(jp.jasa) || 0;
            colorIdx++;
        });

        // Pie chart — Pokok (pakai piePokok)
        var myChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    label: 'Angsuran Pokok',
                    backgroundColor: pieBgColors,
                    borderWidth: 1,
                    data: piePokok,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                }
            }
        });

        $('#total_angsur_pokok').html(formatter.format(total_pokok));
        $('#total_angsur_jasa').html(formatter.format(total_jasa));

        let childWindow, loading;
        $(document).on('click', '#simpanSaldo', function(e) {
            var link = $(this).attr('data-href')

            loading = Swal.fire({
                title: "Mohon Menunggu..",
                html: "Menyimpan Saldo Januari sampai Desember Th. {{ date('Y') }}",
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            })

            childWindow = window.open(link, '_blank');
        })

        window.addEventListener('message', function(event) {
            if (event.data === 'closed') {
                loading.close()
                window.location.reload()
            }
        })
    </script>
@endsection
