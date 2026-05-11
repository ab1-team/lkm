@php
    use App\Models\Kecamatan;
    $kecamatan = Kecamatan::where('web_kec', explode('//', URL::to('/'))[1])
        ->orWhere('web_alternatif', explode('//', URL::to('/'))[1])
        ->first();
    use App\Utils\Tanggal;
    $waktu = date('H:i');
    $tempat = '';

    $sum_pokok = 0;
    if ($real) {
        $sum_pokok = $real->sum_pokok;
    }
    $saldo_pokok = $perguliran->alokasi - $sum_pokok;
    if ($saldo_pokok < 0) {
        $saldo_pokok = 0;
    }
    
    $dokumen_proposal = [
        ['title' => 'Cover', 'file' => 'coverProposal', 'withExcel' => false],
        ['title' => 'Check List', 'file' => 'check', 'withExcel' => false],
        ['title' => 'Surat Pengajuan Pinjaman', 'file' => 'suratPengajuanPinjaman', 'withExcel' => false],
        ['title' => 'Surat Rekomendasi Kredit', 'file' => 'suratRekomendasi', 'withExcel' => false],
        ['title' => 'Profil Kelompok', 'file' => 'profilKelompok', 'withExcel' => false],
        ['title' => 'Susunan Pengurus', 'file' => 'susunanPengurus', 'withExcel' => false],
        ['title' => 'Daftar Anggota', 'file' => 'anggotaKelompok', 'withExcel' => false],
        ['title' => 'Daftar Pemanfaat', 'file' => 'daftarPemanfaat', 'withExcel' => false],
        ['title' => 'Pernyataan Tanggung Renteng', 'file' => 'tanggungRenteng', 'withExcel' => false],
        ['title' => 'FC KTP', 'file' => 'fotoCopyKTP', 'withExcel' => false],
        ['title' => 'Pernyataan Peminjam', 'file' => 'pernyataanPeminjam', 'withExcel' => false],
        ['title' => 'BA Musyawarah Desa', 'file' => 'baMusyawarahDesa', 'withExcel' => false],
        ['title' => 'Form Verifikasi', 'file' => 'formVerifikasi', 'withExcel' => false],
        ['title' => 'Daftar Hadir Verifikasi', 'file' => 'daftarHadirVerifikasi', 'withExcel' => false],
        ['title' => 'Rencana Angsuran', 'file' => 'rencanaAngsuran', 'withExcel' => false],
    ];

    $dokumen_pencairan = [
        ['title' => 'Cover', 'file' => 'coverPencairan', 'withExcel' => false],
        ['title' => 'Surat Perjanjian Kredit (SPK)', 'file' => 'spk', 'withExcel' => false],
        ['title' => 'Surat Kelayakan', 'file' => 'suratKelayakan', 'withExcel' => false],
        ['title' => 'Surat Kuasa', 'file' => 'suratKuasa', 'withExcel' => false],
        ['title' => 'Berita Acara Pencairan', 'file' => 'BaPencairan', 'withExcel' => false],
        ['title' => 'Daftar Hadir Pencairan', 'file' => 'daftarHadirPencairan', 'withExcel' => false],
        ['title' => 'Tanda Terima', 'file' => 'tandaTerima', 'withExcel' => false],
        ['title' => 'Kuitansi', 'file' => 'kuitansi', 'withExcel' => false],
        ['title' => 'Kartu Angsuran', 'file' => 'kartuAngsuran', 'withExcel' => false],
        ['title' => 'Rencana Angsuran', 'file' => 'rencanaAngsuran', 'withExcel' => false],
        ['title' => 'Rekening Koran', 'file' => 'rekeningKoran', 'withExcel' => false],
        ['title' => 'Pemberitahuan Ke Desa', 'file' => 'pemberitahuanDesa', 'withExcel' => false],
        ['title' => 'Surat Ahli Waris', 'file' => 'suratAhliWaris', 'withExcel' => false],
        ['title' => 'Surat Tagihan', 'file' => 'suratTagihan', 'withExcel' => false],
    ];

@endphp
@extends('layouts.base')

@section('content')
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-users icon-gradient bg-sunny-morning"></i>
                    </div>
                    <div>
                        <b style="color: #ffffff; font-size: 1.1rem; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                            Kelompok {{ $perguliran->kelompok->nama_kelompok }} Loan ID. {{ $perguliran->id }}
                            ({{ $perguliran->jpp->nama_jpp }})
                        </b>
                        <div class="page-title-subheading mt-1">
                            <span style="display: inline-block; padding: 4px 10px; margin: 0 4px 4px 0; border-radius: 4px; background: rgba(0,229,255,0.2); color: #00e5ff; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; text-shadow: none; border: 1px solid rgba(0,229,255,0.6);">
                                {{ $perguliran->kelompok->kd_kelompok }}
                            </span>
                            <span style="display: inline-block; padding: 4px 10px; margin: 0 4px 4px 0; border-radius: 4px; background: rgba(0,229,255,0.2); color: #00e5ff; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; text-shadow: none; border: 1px solid rgba(0,229,255,0.6);">
                                {{ $perguliran->kelompok->alamat_kelompok }}
                            </span>
                            <span style="display: inline-block; padding: 4px 10px; margin: 0 4px 4px 0; border-radius: 4px; background: rgba(105,255,71,0.2); color: #69ff47; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; text-shadow: none; border: 1px solid rgba(105,255,71,0.6);">
                                {{ $perguliran->kelompok->d->sebutan_desa->sebutan_desa }}
                                {{ $perguliran->kelompok->d->nama_desa }}
                            </span>
                            <span style="display: inline-block; padding: 4px 10px; margin: 0 4px 4px 0; border-radius: 4px; background: rgba(105,255,71,0.2); color: #69ff47; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.5px; text-shadow: none; border: 1px solid rgba(105,255,71,0.6);">
                                Anggota: {{ $perguliran->pinjaman_anggota_count ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
                @if ($perguliran->status == 'P')
                    <div class="page-title-actions">
                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom"
                            class="btn-shadow me-3 btn btn-success" id="BtnEditProposal">
                            <i class="fa fa-edit"></i>&nbsp; EDIT PROPOSAL
                        </button>
                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="bottom"
                            class="btn-shadow me-3 btn btn-danger" id="HapusProposal">
                            <i class="fa fa-trash"></i>&nbsp; HAPUS PROPOSAL
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div id="layout">
        </div>
        <div class="d-flex justify-content-end mt-2 px-3" style="margin-bottom: 60px !important;">
            @if ($perguliran->status == 'L' || $perguliran->status == 'H')
                @if ($perguliran->status != 'H')
                    <button type="button" data-bs-toggle="tooltip"
                        onclick="window.open('/cetak_keterangan_lunas/{{ $perguliran->id }}')" type="button"
                        class="btn-shadow me-2 btn btn-danger mb-0">
                        <i class="fa fa-print me-1"></i> Cetak Keterangan Pelunasan
                    </button>
                @endif
            @endif
            <a href="/perguliran?status={{ $perguliran->status }}" class="btn bg-gradient-secondary text-white mb-0 shadow-sm font-weight-bold">
                <i class="fa fa-reply-all me-1"></i> KEMBALI
            </a>
        </div>
    </div>
@endsection

@section('modal')
    <div id="placeholder" class="d-none">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <span class="placeholder rounded-circle border" style="width: 150px; height: 150px;"></span>

                        <h5 class="mb-2">
                            <b><span class="placeholder col-12"></span></b>
                        </h5>

                        <div class="text-muted">
                            <span class="placeholder col-12"></span>
                        </div>
                        <div class="text-muted"><span class="placeholder col-12"></span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="alert placeholder col-12 p-4"></div>
                <div class="alert placeholder col-12 p-5"></div>
                <div class="alert placeholder col-12 p-5"></div>
                <div class="alert placeholder col-12 p-4"></div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Proposal --}}
    <div class="modal fade" id="EditProposal" tabindex="-1" aria-labelledby="EditProposalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="EditProposalLabel">
                        <b>
                            Edit Proposal Kelompok {{ $perguliran->kelompok->nama_kelompok }}
                            Loan ID.
                        </b>
                        <span class="btn btn-primary">{{ $perguliran->id }}</span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="LayoutEditProposal">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanEditProposal" class="btn btn-dark btn-sm">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Cetak Dokumen Proposal --}}
    <div class="modal fade" id="CetakDokumenProposal" tabindex="-1" aria-labelledby="CetakDokumenProposalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="CetakDokumenProposalLabel">Cetak Dokumen Proposal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="position-relative mb-3">
                                <label for="tglProposal" class="form-label">Tanggal Proposal</label>
                                <input autocomplete="off" type="text" name="tglProposal" id="tglProposal"
                                    class="form-control" readonly
                                    value="{{ Tanggal::tglIndo($perguliran->tgl_proposal) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="position-relative mb-3">
                                <label for="tglVerifikasi" class="form-label">Tanggal Verifikasi</label>
                                <input autocomplete="off" type="text" name="tglVerifikasi" id="tglVerifikasi"
                                    class="form-control" readonly
                                    value="{{ Tanggal::tglIndo($perguliran->tgl_verifikasi) }}">
                            </div>
                        </div>

                        <form action="/perguliran/dokumen?status=P" target="_blank" method="post">
                            @csrf

                            <input type="hidden" name="id" value="{{ $perguliran->id }}">
                            <div class="row">
                                @foreach ($dokumen_proposal as $doc => $val)
                                    <div class="col-md-3 d-grid">
                                        @if ($val['withExcel'])
                                            <div class="btn-group">
                                                <button class="btn btn-primary flex-grow-1 me-2"
                                                    style="background-color: rgb(2, 111, 254); text-start" type="submit"
                                                    name="report" value="{{ $val['file'] }}#pdf">
                                                    {{ $loop->iteration }}. {{ $val['title'] }}
                                                </button><br>
                                                <button class="btn btn-primary flex-grow-1 me-2"
                                                    style="background-color :rgb(2, 111, 254);" type="submit"
                                                    name="report" value="{{ $val['file'] }}#excel">
                                                    <i class="fas fa-file-excel"></i>
                                                </button>
                                            </div>
                                        @else
                                            <button class="btn btn-primary flex-grow-1 me-2 text-start"
                                                style="background-color: rgb(2, 111, 254);" type="submit" name="report"
                                                value="{{ $val['file'] }}#pdf">
                                                {{ $loop->iteration }}. {{ $val['title'] }}
                                            </button>
                                            <br>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        @php
            $readonly = 'readonly';
            if ($perguliran->status == 'W') {
                $readonly = '';
            }

            $wt_cair = explode('_', $perguliran->wt_cair);
            if (count($wt_cair) == 1) {
                $waktu = $wt_cair[0];
            }

            if (count($wt_cair) == 2) {
                $waktu = $wt_cair[0];
                $tempat = $wt_cair[1];
            }
        @endphp
    </div>

    {{-- Modal Cetak Dokumen Pencairan --}}
    <div class="modal fade" id="CetakDokumenPencairan" tabindex="-1" aria-labelledby="CetakDokumenPencairanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="CetakDokumenPencairanLabel">Cetak Dokumen Pencairan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/perguliran/simpan_data/{{ $perguliran->id }}?jenis=dokumen_pencairan" method="post"
                        id="simpanData">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="position-relative mb-3">
                                    <label for="spk_no" class="form-label">Nomor SPK</label>
                                    <input autocomplete="off" type="text" name="spk_no" id="spk_no"
                                        class="form-control save" {{ $readonly }}
                                        value="{{ $perguliran->spk_no }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative mb-3">
                                    <label for="tempat" class="form-label">Tempat</label>
                                    <input autocomplete="off" type="text" name="tempat" id="tempat"
                                        class="form-control save" {{ $readonly }} value="{{ $tempat }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="position-relative mb-3">
                                    <label for="tgl_cair" class="form-label">Tanggal Cair</label>
                                    <input autocomplete="off" type="text" name="tgl_cair" id="_tgl_cair"
                                        class="form-control date save" {{ $readonly }}
                                        value="{{ Tanggal::tglIndo($perguliran->tgl_cair) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative mb-3">
                                    <label for="waktu" class="form-label">Waktu</label>
                                    <input autocomplete="off" type="text" name="waktu" id="waktu"
                                        class="form-control save" {{ $readonly }} value="{{ $waktu }}">
                                </div>
                            </div>
                        </div>
                    </form>

                    <form action="/perguliran/dokumen?status={{ $perguliran->status }}" target="_blank"
                        method="post">
                        @csrf

                        <input type="hidden" name="id" value="{{ $perguliran->id }}">
                        <div class="row">
                            @foreach ($dokumen_pencairan as $doc => $val)
                                <div class="col-md-3 d-grid">
                                    @if ($val['withExcel'])
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-sm text-start" type="submit" name="report"
                                                value="{{ $val['file'] }}#pdf">
                                                {{ $loop->iteration }}. {{ $val['title'] }}
                                            </button>
                                            <button class="btn btn-icon btn-sm btn-instagram" type="submit"
                                                name="report" value="{{ $val['file'] }}#excel">
                                                <i class="fas fa-file-excel"></i>
                                            </button>
                                        </div>
                                    @else
                                        <button class="btn btn-info btn-sm text-start" type="submit" name="report"
                                            value="{{ $val['file'] }}#pdf">
                                            {{ $loop->iteration }}. {{ $val['title'] }}
                                        </button><br>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Pemanfaat --}}
    <div class="modal fade" id="TambahPemanfaat" tabindex="-1" aria-labelledby="TambahPemanfaatLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="TambahPemanfaatLabel">
                        Tambah Calon Pemanfaat ({{ $perguliran->id }})
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/pinjaman_anggota" method="post" id="FormTambahPemanfaat">
                        @csrf

                        <input type="hidden" name="id_pinkel" id="id_pinkel" value="{{ $perguliran->id }}">
                        <input type="hidden" name="nia_pemanfaat" id="nia_pemanfaat">
                        <input type="hidden" name="catatan_pinjaman" id="catatan_pinjaman">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="position-relative">
                                    <input type="text" id="cariNik" name="cariNik" class="form-control"
                                        placeholder="Ketikkan NIK atau Nama" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="position-relative">
                                    <input type="text" id="alokasi_pengajuan" disabled name="alokasi_pengajuan"
                                        class="form-control money" placeholder="Alokasi Pengajuan">
                                </div>
                                <small class="text-danger" id="msg_alokasi_pengajuan"></small>
                            </div>
                        </div>

                        <div class="fw-bold text-center">
                            <div>
                                {{ $perguliran->kelompok->nama_kelompok }} -
                                {{ $perguliran->kelompok->d->sebutan_desa->sebutan_desa }}
                                {{ $perguliran->kelompok->d->nama_desa }}
                            </div>
                        </div>
                    </form>

                    <div class="card border">
                        <div class="card-body pt-3 pb-0 ps-3 pe-3">
                            <div id="LayoutTambahPemanfaat">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="ImportPemanfaat" class="btn btn-outline-info btn-sm">
                        Import Pemanfaat
                    </button>
                    <button type="button" id="SimpanPemanfaat" disabled class="btn btn-dark btn-sm">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Import Pemanfaat --}}
    <div class="modal fade" id="ImportPemanfaatModal" tabindex="-1" aria-labelledby="ImportPemanfaatModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ImportPemanfaatModalLabel">
                        Import Pemanfaat
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/pinjaman_anggota/import" method="post" id="FormImportPemanfaat">
                        @csrf

                        <input type="hidden" name="id_pinkel" id="id_pinkel" value="{{ $perguliran->id }}">
                        <div id="LayoutImportPemanfaatModal"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanImportPemanfaat" class="btn btn-dark btn-sm">
                        Import Pemanfaat
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Rescedule Pinjaman --}}
    <div class="modal fade" id="Rescedule" tabindex="-1" aria-labelledby="ResceduleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ResceduleLabel">
                        Resceduling <span class="badge badge-info">Loan ID. {{ $perguliran->id }}</span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-justify">
                        Fitur ini dapat Anda gunakan jika Anda akan menjadwal ulang (<b>Resceduling</b>) Pinjaman.
                        Dengan klik tombol <b>Rescedule Pinjaman</b> maka pinjaman ini akan berstatus <b>R</b>, dan
                        akan
                        membuat pinjaman baru dengan Alokasi sebesar saldo yang ada, yaitu
                        <b>Rp. {{ number_format($saldo_pokok) }}</b> ;
                    </div>

                    <form action="/perguliran/rescedule" method="post" id="formRescedule">
                        @csrf

                        <input type="hidden" name="id" id="id" value="{{ $perguliran->id }}">
                        <input type="hidden" name="_pengajuan" id="_pengajuan" value="{{ $saldo_pokok }}">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="tgl_resceduling">Tanggal Resceduling</label>
                                        <input autocomplete="off" type="text" name="tgl_resceduling"
                                            id="tgl_resceduling" class="form-control date" value="{{ date('d/m/Y') }}">
                                        <small class="text-danger" id="msg_tgl_resceduling"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="pengajuan">Pengajuan Rp.</label>
                                        <input autocomplete="off" type="text" name="pengajuan" id="pengajuan"
                                            class="form-control money" disabled
                                            value="{{ number_format($saldo_pokok, 2) }}">
                                        <small class="text-danger" id="msg_pengajuan"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label class="form-label" for="sistem_angsuran_pokok">Sistem Angs. Pokok</label>
                                        <select class="js-example-basic-single form-control" name="sistem_angsuran_pokok"
                                            id="sistem_angsuran_pokok">
                                            @foreach ($sistem_angsuran as $sa)
                                                <option {{ $perguliran->sistem_angsuran == $sa->id ? 'selected' : '' }}
                                                    value="{{ $sa->id }}">
                                                    {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-danger" id="msg_sistem_angsuran_pokok"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label class="form-label" for="sistem_angsuran_jasa">Sistem Angs. Jasa</label>
                                        <select class="js-example-basic-single form-control" name="sistem_angsuran_jasa"
                                            id="sistem_angsuran_jasa">
                                            @foreach ($sistem_angsuran as $sa)
                                                <option {{ $perguliran->sa_jasa == $sa->id ? 'selected' : '' }}
                                                    value="{{ $sa->id }}">
                                                    {{ $sa->nama_sistem }} ({{ $sa->deskripsi_sistem }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-danger" id="msg_sistem_angsuran_jasa"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="jangka">Jangka</label>
                                        <input autocomplete="off" type="number" name="jangka" id="jangka"
                                            class="form-control" value="{{ $perguliran->jangka }}">
                                        <small class="text-danger" id="msg_jangka"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="pros_jasa">Prosentase Jasa (%)</label>
                                        <input autocomplete="off" type="number" name="pros_jasa" id="pros_jasa"
                                            class="form-control" value="{{ $perguliran->pros_jasa }}">
                                        <small class="text-danger" id="msg_pros_jasa"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanRescedule" class="btn btn-dark btn-sm">
                        Rescedule Pinjaman
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Penghapusan Pinjaman --}}
    <div class="modal fade" id="Penghapusan" tabindex="-1" aria-labelledby="PenghapusanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="PenghapusanLabel">
                        Penghapusan Piutang <span class="badge badge-info">Loan ID. {{ $perguliran->id }}</span>
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="/perguliran/hapus" method="post" id="FormPenghapusanPinjaman">
                        @csrf

                        <input type="hidden" name="id" id="id" value="{{ $perguliran->id }}">
                        <input type="hidden" name="saldo" id="saldo" value="{{ $saldo_pokok }}">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="tgl_penghapusan">Tanggal Penghapusan</label>
                                        <input autocomplete="off" type="text" name="tgl_penghapusan"
                                            id="tgl_penghapusan" class="form-control date" value="{{ date('d/m/Y') }}">
                                        <small class="text-danger" id="msg_tgl_penghapusan"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="alokasi">Alokasi Rp.</label>
                                        <input autocomplete="off" type="text" name="alokasi" id="alokasi"
                                            class="form-control money" disabled
                                            value="{{ number_format($saldo_pokok, 2) }}">
                                        <small class="text-danger" id="msg_alokasi"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="position-relative mb-3">
                                        <label for="alasan_penghapusan">Alasan Penghapusan</label>
                                        <textarea class="form-control" name="alasan_penghapusan" id="alasan_penghapusan"></textarea>
                                        <small class="text-danger" id="msg_alasan_penghapusan"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="SimpanHapus" class="btn btn-dark btn-sm">
                        Hapus Pinjaman
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form action="/perguliran/{{ $perguliran->id }}" method="post" id="FormDeleteProposal">
        @csrf
        @method('DELETE')
    </form>

    <div id="placeholder" class="d-none">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <span class="placeholder rounded-circle border" style="width: 150px; height: 150px;"></span>

                        <h5 class="mb-2">
                            <b><span class="placeholder col-12"></span></b>
                        </h5>

                        <div class="text-muted">
                            <span class="placeholder col-12"></span>
                        </div>
                        <div class="text-muted"><span class="placeholder col-12"></span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="alert placeholder col-12 p-4"></div>
                <div class="alert placeholder col-12 p-5"></div>
                <div class="alert placeholder col-12 p-5"></div>
                <div class="alert placeholder col-12 p-4"></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.date').datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
        var formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })

        $('.js-example-basic-single').select2({
            theme: 'bootstrap-5'
        });
        
        $(".money").maskMoney();

        // Pastikan DOM sudah siap
        $(document).ready(function() {
            $.get('/perguliran/{{ $perguliran->id }}', function(result) {
                $('#layout').html(result)
            })
        });

        $('#BtnEditProposal').click(function(e) {
            e.preventDefault()

            $.get('/perguliran/{{ $perguliran->id }}/edit', function(result) {
                $('#LayoutEditProposal').html(result)
                // Gunakan jQuery untuk modal
                $('#EditProposal').modal('show')
            })
        })

        $('#HapusProposal').click(function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Hapus Proposal Ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#FormDeleteProposal')
                    $.ajax({
                        type: 'post',
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.hapus) {
                                Swal.fire('Berhasil!', result.msg, 'success').then(() => {
                                    window.location.href = '/perguliran'
                                })
                            } else {
                                Swal.fire('Peringatan', result.msg, 'warning')
                            }
                        }
                    })
                }
            })
        })

        // Typeahead untuk pencarian NIK
        $(document).ready(function() {
            if ($('#cariNik').length) {
                $('#cariNik').typeahead({
                    source: function(query, process) {
                        var states = [];
                        return $.get('/pinjaman_anggota/cari_pemanfaat', {
                            loan_id: '{{ $perguliran->id }}',
                            query: query
                        }, function(result) {
                            var resultList = result.map(function(item) {
                                var disable = false
                                if (item.status == '0') {
                                    disable = true
                                }

                                states.push({
                                    "id": item.id,
                                    "name": item.namadepan + ' [' + item.nik + ']' + '[' + item.alamat + ']',
                                    "value": item.nik,
                                    "id_pinkel": '{{ $perguliran->id }}',
                                    "disable": disable
                                });
                            });

                            return process(states);
                        })
                    },
                    afterSelect: function(item) {
                        if (item != '') {
                            $.ajax({
                                url: '/pinjaman_anggota/register/' + item.id_pinkel,
                                type: 'get',
                                data: item,
                                success: function(result) {
                                    if (result.enable_alokasi) {
                                        $('#alokasi_pengajuan').removeAttr('disabled')
                                        $('#SimpanPemanfaat').removeAttr('disabled')
                                    } else {
                                        $('#alokasi_pengajuan').attr('disabled', true)
                                        $('#SimpanPemanfaat').attr('disabled', true)
                                    }

                                    $('#nia_pemanfaat').val(result.nia)
                                    $('#catatan_pinjaman').val(result.catatan)
                                    $('#LayoutTambahPemanfaat').html(result.html)
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Pemanfaat diblokir. Tidak dapat mengajukan pinjaman', 'error')
                        }
                    }
                });
            }
        });

        $(document).on('click', '#SimpanEditProposal', function(e) {
            e.preventDefault()
            $('small').html('')

            var form = $('#FormEditProposal')
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    Swal.fire('Berhasil', result.msg, 'success').then(() => {
                        $.get('/perguliran/{{ $perguliran->id }}', function(result) {
                            $('#layout').html(result)
                            $('#EditProposal').modal('hide')
                            window.location.reload()
                        })
                    })
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static').addClass('is-invalid')
                        $('#FormEditProposal #msg_' + key).html(res)
                    })
                }
            })
        })

        $(document).on('change', '.save', function() {
            var form = $('#simpanData')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        $('[name=tgl_cair]').val(result.tgl_cair)
                        Swal.fire('Berhasil', result.msg, 'success')
                    }
                }
            })
        })

        // HANDLER UNTUK TOMBOL SIMPAN - MENDUKUNG VERIFIKASI DAN WAITING
        $(document).on('click', '#Simpan', async function(e) {
            e.preventDefault()
            $('small').html('')

            var status = $('#status').val()
            
            if (status == 'A') {
                var alokasi = $('#alokasi').val() ? parseInt($('#alokasi').val().split(',').join('').split('.00').join('')) : 0

                var lanjut = true;
                lanjut = await Swal.fire({
                    title: 'Peringatan',
                    text: 'Anda akan melakukan Pencairan Piutang sebesar Rp. ' + $('#alokasi').val().split('.00').join('') +
                        ' untuk kelompok tersebut? Setelah klik tombol Lanjutkan data tidak dapat diubah kembali !',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    icon: 'warning'
                }).then((result) => {
                    if (result.isConfirmed) {
                        return true
                    }
                    return false
                })

                if (lanjut) {
                    var form = $('#FormInput')
                    var formData = form.serialize()
                    
                    var tglCair = $('#tgl_cair').val()
                    if (tglCair) {
                        formData += '&tgl_tunggu=' + encodeURIComponent(tglCair)
                    }
                    
                    $.ajax({
                        type: 'POST',
                        url: form.attr('action') + '?save=true',
                        data: formData,
                        success: function(result) {
                            if (result.success) {
                                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                    window.location.href = '/detail/' + result.id
                                })
                            } else {
                                Swal.fire('Error', result.msg, 'error')
                            }
                        },
                        error: function(result) {
                            const respons = result.responseJSON;
                            Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                            $.map(respons, function(res, key) {
                                $('#' + key).parent('.input-group.input-group-static').addClass('is-invalid')
                                $('#msg_' + key).html(res)
                            })
                        }
                    })
                }
            }
            else if (status == 'W') {
                var alokasi = $('#alokasi').val() ? parseInt($('#alokasi').val().split(',').join('').split('.00').join('')) : 0
                var __alokasi = $('#__alokasi').val() ? parseInt($('#__alokasi').val()) : 0

                var lanjut = true;
                if (alokasi != __alokasi) {
                    lanjut = await Swal.fire({
                        title: 'Peringatan',
                        text: 'Jumlah alokasi Anggota dan Kelompok Berbeda. Tetap lanjutkan?',
                        showCancelButton: true,
                        confirmButtonText: 'Lanjutkan',
                        cancelButtonText: 'Batal',
                        icon: 'warning'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            return true;
                        }
                        return false
                    })
                }

                if (lanjut) {
                    var form = $('#FormInput')
                    var formData = form.serialize()
                    
                    var tglCair = $('#tgl_cair').val()
                    if (tglCair) {
                        formData += '&tgl_tunggu=' + encodeURIComponent(tglCair)
                    }
                    
                    $.ajax({
                        type: 'POST',
                        url: form.attr('action') + '?save=true',
                        data: formData,
                        success: function(result) {
                            Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                window.location.href = '/detail/' + result.id
                            })
                        },
                        error: function(result) {
                            const respons = result.responseJSON;
                            Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                            $.map(respons, function(res, key) {
                                $('#' + key).parent('.input-group.input-group-static').addClass('is-invalid')
                                $('#msg_' + key).html(res)
                            })
                        }
                    })
                }
            }
        })

        $(document).on('click', '#kembaliProposal', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Peringatan',
                text: 'Anda akan mengembalikan data ke tahap proposal',
                showCancelButton: true,
                confirmButtonText: 'Kembalikan',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#formKembaliProposal')

                    $.ajax({
                        type: 'post',
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                window.location.href = '/detail/' + result.id
                            })
                        }
                    })
                }
            })
        })

        $(document).on('click', '#tidakLayakDicairkan', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Peringatan',
                text: 'Anda akan menandai pinjaman ini sebagai tidak layak dicairkan',
                showCancelButton: true,
                confirmButtonText: 'Tandai',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#formTidakLayakDicairkan')

                    $.ajax({
                        type: 'post',
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                window.location.href = '/perguliran'
                            })
                        }
                    })
                }
            })
        })

        $(document).on('click', '#SimpanRescedule', async function(e) {
            e.preventDefault()
            $('#Rescedule').modal('hide')
            
            // Hapus backdrop secara manual
            setTimeout(function() {
                $('.modal-backdrop').remove()
                $('body').removeClass('modal-open')
            }, 500);

            const {
                value: spk
            } = await Swal.fire({
                title: 'Masukkan Nomor SPK baru',
                input: 'text',
                inputLabel: 'Spk No.',
                confirmButtonText: 'Simpan Perubahan'
            })

            if (spk) {
                var form = $('#formRescedule')
                $.ajax({
                    type: 'POST',
                    url: form.attr('action') + '?spk=' + spk,
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success) {
                            var id = result.id
                            $.get('/perguliran/generate/' + result.id + '?status=' + result.status + '&save',
                                function(result) {
                                    if (result.success) {
                                        Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                            window.location.href = '/detail/' + id
                                        })
                                    }
                                })
                        }
                    }
                })
            }
        })

        $(document).on('click', '#SimpanHapus', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Peringatan',
                text: 'Anda yakin ingin melakukan penghapusan untuk pinjaman ini?',
                showCancelButton: true,
                confirmButtonText: 'Hapus Pinjaman',
                cancelButtonText: 'Batal',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#FormPenghapusanPinjaman')
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.success) {
                                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                    window.location.href = '/perguliran'
                                })
                            }
                        }
                    })
                }
            })
        })

        $(document).on('click', '.btn-link', function(e) {
            var action = $(this).attr('data-action')
            open_window(action)
        })

        // Fungsi Tambah Pemanfaat
        $('#cariNik').typeahead({
            source: function(query, process) {
                var states = [];
                return $.get('/pinjaman_anggota/cari_pemanfaat', {
                    loan_id: '{{ $perguliran->id }}',
                    query: query
                }, function(result) {
                    var resultList = result.map(function(item) {

                        var disable = false
                        if (item.status == '0') {
                            disable = true
                        }

                        states.push({
                            "id": item.id,
                            "name": item.namadepan + ' [' + item.nik + ']' + '[' + item
                                .alamat + ']',
                            "value": item.nik,
                            "id_pinkel": '{{ $perguliran->id }}',
                            "disable": disable
                        });
                    });

                    return process(states);
                })
            },
            afterSelect: function(item) {
                if (item != '') {
                    $.ajax({
                        url: '/pinjaman_anggota/register/' + item.id_pinkel,
                        type: 'get',
                        data: item,
                        success: function(result) {
                            if (result.enable_alokasi) {
                                $('#alokasi_pengajuan').removeAttr('disabled')
                                $('#SimpanPemanfaat').removeAttr('disabled')
                            } else {
                                $('#alokasi_pengajuan').attr('disabled', true)
                                $('#SimpanPemanfaat').attr('disabled', true)
                            }

                            $('#nia_pemanfaat').val(result.nia)
                            $('#catatan_pinjaman').val(result.catatan)
                            $('#LayoutTambahPemanfaat').html(result.html)
                        }
                    });
                } else {
                    Swal.fire('Error', 'Pemanfaat diblokir. Tidak dapat mengajukan pinjaman', 'error')
                }
            }
        });

        $(document).on('click', '#BtnTambahPemanfaat', function(e) {
            e.preventDefault()
            $('small').html('')

            $('#cariNik').val('')
            $('#alokasi_pengajuan').val('')

            $('#LayoutTambahPemanfaat').html($('#placeholder').html())
        })

        $(document).on('click', '#ImportPemanfaat', function(e) {
            e.preventDefault()

            $('#TambahPemanfaat').modal('hide')
            
            // Hapus backdrop dan tunggu sebentar sebelum buka modal baru
            setTimeout(function() {
                $('.modal-backdrop').remove()
                $('body').removeClass('modal-open')
                $('body').css('padding-right', '')
                
                $.get('/pinjaman_anggota/ambil_daftar_pemanfaat', {
                    'id_kelompok': '{{ $perguliran->id_kel }}',
                    'id_pinkel': '{{ $perguliran->id }}'
                }, function(result) {
                    if (result.success) {
                        $('#LayoutImportPemanfaatModal').html(result.view)
                        $('#ImportPemanfaatModal').modal('show')
                    }
                })
            }, 300);
        })

        $(document).on('click', '#SimpanImportPemanfaat', function(e) {
            e.preventDefault()

            var btn = $(this)
            if (btn.attr('data-processing') === 'true') {
                return false
            }
            btn.attr('data-processing', 'true')
            btn.prop('disabled', true)

            var form = $('#FormImportPemanfaat')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    $('#ImportPemanfaatModal').modal('hide')
                    
                    // Hapus backdrop secara manual
                    setTimeout(function() {
                        $('.modal-backdrop').remove()
                        $('body').removeClass('modal-open')
                        $('body').css('padding-right', '')
                    }, 300);

                    if (result.success) {
                        Swal.fire('Berhasil', result.msg, 'success').then(() => {
                            $.get('/perguliran/{{ $perguliran->id }}', function(result) {
                                $('#layout').html(result)
                            })
                            
                            btn.attr('data-processing', 'false')
                            btn.prop('disabled', false)
                        })
                    } else {
                        Swal.fire('Error', result.msg, 'error')
                        btn.attr('data-processing', 'false')
                        btn.prop('disabled', false)
                    }
                }
            })
        })

        // Fungsi Simpan Pemanfaat - Unbind dulu untuk mencegah double binding
        $(document).off('click', '#SimpanPemanfaat').on('click', '#SimpanPemanfaat', function(e) {
            e.preventDefault()
            $('small').html('')

            // Disable button untuk mencegah double click
            var btn = $(this)
            if (btn.attr('data-processing') === 'true') {
                return false
            }
            btn.attr('data-processing', 'true')
            btn.prop('disabled', true)

            var form = $('#FormTambahPemanfaat')
            $.ajax({
                type: 'post',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    $('#TambahPemanfaat').modal('hide')
                    
                    // Hapus backdrop secara manual
                    setTimeout(function() {
                        $('.modal-backdrop').remove()
                        $('body').removeClass('modal-open')
                        $('body').css('padding-right', '')
                    }, 300);

                    Swal.fire('Berhasil', result.msg, 'success').then(() => {
                        $.get('/perguliran/{{ $perguliran->id }}', function(result) {
                            $('#layout').html(result)
                        })
                        
                        // Reset form dan button
                        $('#cariNik').val('')
                        $('#alokasi_pengajuan').val('')
                        $('#LayoutTambahPemanfaat').html('')
                        btn.attr('data-processing', 'false')
                        btn.prop('disabled', true)
                    })
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.position-relative').addClass('is-invalid')
                        $('#FormTambahPemanfaat #msg_' + key).html(res)
                    })
                    btn.attr('data-processing', 'false')
                    btn.prop('disabled', false)
                }
            })
        })

        $(document).on('click', '.HapusPinjamanAnggota', function(e) {
            e.preventDefault()

            var id = $(this).attr('id')
            Swal.fire({
                title: 'Hapus Pemanfaat Ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'get',
                        url: '/hapus_pemanfaat/' + id,
                        data: {
                            'id': id
                        },
                        success: function(result) {
                            if (result.hapus) {
                                Swal.fire('Berhasil', result.msg, 'success').then(() => {
                                    $.get('/perguliran/{{ $perguliran->id }}',
                                        function(result) {
                                            $('#layout').html(result)
                                        })
                                })
                            } else {
                                Swal.fire('Berhasil', result.msg, 'warning')
                            }
                        }
                    })
                }
            })
        })
    </script>
@endsection
