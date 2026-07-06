<div class="row">
    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h5 class="card-title">Proposal</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Tgl Pengajuan
                                        <div class="badge angka-warna-biru">
                                            {{ Tanggal::tglIndo($perguliran->tgl_proposal) }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Pengajuan
                                        <div class="badge angka-warna-biru">
                                            {{ number_format($perguliran->proposal) }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Jenis Jasa
                                        <div class="badge angka-warna-biru">
                                            {{ $perguliran->jasa->nama_jj }}
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="col">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h5 class="card-title">&nbsp;</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Jasa
                                        <div class="badge angka-warna-biru">
                                            {{ $perguliran->pros_jasa . '% / ' . $perguliran->jangka . ' bulan' }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Angs. Pokok
                                        <div class="badge angka-warna-biru">
                                            {{ $perguliran->sis_pokok->nama_sistem }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Angs. Jasa
                                        <div class="badge angka-warna-biru">
                                            {{ $perguliran->sis_jasa->nama_sistem }}
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h5 class="card-title">Verified</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Tgl Verifikasi
                                        <div class="badge angka-warna-merah">
                                            {{ Tanggal::tglIndo($perguliran->tgl_verifikasi) }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Verifikasi
                                        <div class="badge angka-warna-merah">
                                            {{ number_format($perguliran->verifikasi) }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Jenis Jasa
                                        <div class="badge angka-warna-merah">
                                            {{ $perguliran->jasa->nama_jj }}
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="col">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h5 class="card-title">&nbsp;</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Jasa
                                        <div class="badge angka-warna-merah">
                                            {{ $perguliran->pros_jasa . '% / ' . $perguliran->jangka . ' bulan' }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Angs. Pokok
                                        <div class="badge angka-warna-merah">
                                            {{ $perguliran->sis_pokok->nama_sistem }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Angs. Jasa
                                        <div class="badge angka-warna-merah">
                                            {{ $perguliran->sis_jasa->nama_sistem }}
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h5 class="card-title">Waiting</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Tgl Penetapan
                                        <div class="badge angka-warna-kuning">
                                            {{ Tanggal::tglIndo($perguliran->tgl_tunggu) }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Pendanaan
                                        <div class="badge angka-warna-kuning">
                                            {{ number_format($perguliran->alokasi) }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Jenis Jasa
                                        <div class="badge angka-warna-kuning">
                                            {{ $perguliran->jasa->nama_jj }}
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="col">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h5 class="card-title">&nbsp;</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Jasa
                                        <div class="badge angka-warna-kuning">
                                            {{ $perguliran->pros_jasa . '% / ' . $perguliran->jangka . ' bulan' }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Angs. Pokok
                                        <div class="badge angka-warna-kuning">
                                            {{ $perguliran->sis_pokok->nama_sistem }}
                                        </div>
                                    </li>
                                    <li class="list-group-item">Angs. Jasa
                                        <div class="badge angka-warna-kuning">
                                            {{ $perguliran->sis_jasa->nama_sistem }}
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (!($perguliran->jenis_pp == '3' && $perguliran->kelompok->fungsi_kelompok == '2'))
    @if ($perguliran->status == 'A' || $perguliran->status == 'R')
        <div class="card card-body p-2 pb-0 mb-3">
            <div class="d-grid">
                <button type="button" id="BtnTambahPemanfaat" data-bs-toggle="modal"
                    data-bs-target="#TambahPemanfaat" class="btn btn-success btn-sm mb-2 btn-shadow me-3">
                    Tambah Pemanfaat
                </button>
            </div>
        </div>
    @endif

    <div class="main-card mb-3 card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-items-center mb-0" width="100%">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Pengajuan</th>
                            <th>Verifikasi</th>
                            <th>Alokasi</th>
                            @if ($perguliran->status == 'A' || $perguliran->status == 'R')
                                <th>&nbsp;</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $proposal = 0;
                            $verifikasi = 0;
                            $alokasi = 0;
                        @endphp
                        @foreach ($perguliran->pinjaman_anggota as $pinjaman_anggota)
                            @php
                                $proposal += $pinjaman_anggota->proposal;
                                $verifikasi += $pinjaman_anggota->verifikasi;
                                $alokasi += $pinjaman_anggota->alokasi;
                            @endphp
                            <tr>
                                <td align="center">{{ $loop->iteration }}</td>
                                <td>
                                    {{ ucwords($pinjaman_anggota->anggota->namadepan) }}
                                    ({{ $pinjaman_anggota->nia }})
                                </td>
                                <td>
                                    {{ number_format($pinjaman_anggota->proposal) }}
                                </td>
                                <td>
                                    {{ number_format($pinjaman_anggota->verifikasi) }}
                                </td>
                                <td>
                                    {{ number_format($pinjaman_anggota->alokasi) }}
                                </td>
                                @if ($perguliran->status == 'A' || $perguliran->status == 'R')
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" id="{{ $pinjaman_anggota->id }}"
                                                class="btn btn-icon btn-sm btn-danger HapusPinjamanAnggota">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Jumlah</th>
                            <th>{{ number_format($proposal) }}</th>
                            <th>{{ number_format($verifikasi) }}</th>
                            <th>{{ number_format($alokasi) }}</th>
                            @if ($perguliran->status == 'A' || $perguliran->status == 'R')
                                <th></th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-body p-2 pb-0 mb-3">
        <div class="row">
            <div class="col-md-6">
                <div class="d-grid">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenProposal"
                        class="btn btn-info btn-sm mb-2">Cetak Dokumen Proposal</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-grid">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#CetakDokumenPencairan"
                        class="btn btn-info btn-sm mb-2">Cetak Dokumen Pencairan</button>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="main-card mb-3 card {{ $perguliran->status == 'T' ? 'd-none' : '' }}">
    <div class="card-body pb-2">
        <h5 class="mb-1">
            Riwayat Angsuran
        </h5>

        <div class="table-responsive">
            <table class="table table-striped align-items-center mb-0" width="100%">
                <thead>
                    <tr class="bg-dark text-white">
                        <th>#</th>
                        <th>Tgl transaksi</th>
                        <th>Pokok</th>
                        <th>Jasa</th>
                        <th>Saldo Pokok</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perguliran->real as $real)
                        <tr>
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="center">{{ Tanggal::tglIndo($real->tgl_transaksi) }}</td>
                            <td align="right">{{ number_format($real->realisasi_pokok) }}</td>
                            <td align="right">{{ number_format($real->realisasi_jasa) }}</td>
                            <td align="right">{{ number_format($real->saldo_pokok) }}</td>
                            <td align="center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-instagram btn-icon-only btn-tooltip"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="btn-inner--icon"><i class="fas fa-file"></i></span>
                                    </button>
                                    <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a class="dropdown-item border-radius-md" target="_blank"
                                                href="/transaksi/dokumen/struk/{{ $real->id }}">
                                                Kuitansi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item border-radius-md" target="_blank"
                                                href="/transaksi/dokumen/struk_matrix/{{ $real->id }}">
                                                Kuitansi Dot Matrix
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item border-radius-md" target="_blank"
                                                href="/transaksi/dokumen/struk_thermal/{{ $real->id }}">
                                                Kuitansi Thermal
                                            </a>
                                        </li>
                                    </ul>
                                    <button type="button" class="btn btn-tumblr btn-icon-only btn-tooltip"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="btn-inner--icon"><i class="fas fa-file-invoice"></i></span>
                                    </button>
                                    <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a class="dropdown-item border-radius-md" target="_blank"
                                                href="/perguliran/dokumen/kartu_angsuran/{{ $real->loan_id }}/{{ $real->id }}">
                                                Kelompok
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item border-radius-md" target="_blank"
                                                href="/perguliran/dokumen/cetak_kartu_angsuran_anggota/{{ $real->loan_id }}/{{ $real->id }}">
                                                Anggota
                                            </a>
                                        </li>
                                    </ul>
                                    <button type="button"
                                        data-action="/transaksi/dokumen/bkm_angsuran/{{ $real->transaksi->idt }}"
                                        class="btn btn-github btn-icon-only btn-tooltip btn-link"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="BKM"
                                        data-container="body" data-animation="true">
                                        <span class="btn-inner--icon"><i
                                                class="fas fa-file-circle-exclamation"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($perguliran->status == 'A')
            <div class="d-flex justify-content-end mt-3" style="gap: .5em;">
                <button type="button" id="btnCatatanBimbingan" class="btn btn-success btn-sm">
                    Catatan Bimbingan
                </button>
                @if (auth()->user()->jabatan == '1' && auth()->user()->level == '1')
                    <button type="button" data-bs-toggle="modal" data-bs-target="#Rescedule"
                        class="btn btn-warning btn-sm">Resceduling Pinjaman</button>
                @endif
                <button type="button" data-bs-toggle="modal" data-bs-target="#Penghapusan"
                    class="btn btn-danger btn-sm">Penghapusan Pinjaman</button>
            </div>
        @endif
    </div>
</div>
