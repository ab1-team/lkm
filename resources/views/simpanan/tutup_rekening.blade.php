@extends('layouts.base')
@section('content')
<div class="app-main__inner">
    <div class="tab-pane fade show active" id="TutupRekening" role="tabpanel">
        <div class="main-card mb-3 card">
            <div class="card-body p-3">
                <div class="widget-content mb-2">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="widget-heading">Konfirmasi Tutup Rekening</div>
                            <div class="widget-subheading">
                                CIF {{ $nia->id }} &mdash; {{ $nia->anggota->namadepan }}
                                ({{ $nia->js->nama_js }})
                            </div>
                        </div>
                        <div class="widget-content-right">
                            <a href="/simpanan/{{ $nia->id }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Peringatan</h6>
                            <p class="mb-0">
                                Tindakan ini akan <strong>menutup rekening secara permanen</strong> dan tidak dapat dibatalkan.
                                Saldo tersisa akan ditarik seluruhnya, dan saldo minimum akan dipotong sebagai biaya admin penutupan buku rekening.
                            </p>
                        </div>

                        <table class="table table-bordered mb-4">
                            <tbody>
                                <tr>
                                    <th style="width: 35%;">Nomor Rekening</th>
                                    <td>{{ $nia->nomor_rekening }}</td>
                                </tr>
                                <tr>
                                    <th>Nasabah</th>
                                    <td>{{ $nia->anggota->namadepan }}</td>
                                </tr>
                                <tr>
                                    <th>Jenis Simpanan</th>
                                    <td>{{ $nia->js->nama_js }}</td>
                                </tr>
                                <tr>
                                    <th>Saldo Saat Ini</th>
                                    <td><strong>Rp {{ number_format($saldoSekarang, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Saldo Minimal ({{ $nia->js->nama_js }})</th>
                                    <td>Rp {{ number_format($saldoMinimal, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-success">
                                    <th>Penarikan ke Nasabah</th>
                                    <td><strong>Rp {{ number_format($penarikanNasabah, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr class="table-danger">
                                    <th>Biaya Admin Penutupan Buku Rekening</th>
                                    <td><strong>Rp {{ number_format($biayaPenutupan, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <th>Saldo Akhir Setelah Penutupan</th>
                                    <td><strong>Rp 0</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <form id="formTutupRekening" action="/simpanan/{{ $nia->id }}/tutup-rekening" method="POST">
                            @csrf
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="konfirmasi" name="konfirmasi" value="1">
                                <label class="form-check-label" for="konfirmasi">
                                    Saya memahami dan menyetujui penutupan rekening ini.
                                </label>
                            </div>
                            <button type="submit" id="btnKonfirmasiTutup" class="btn btn-danger" disabled>
                                <i class="fa fa-lock"></i> Tutup Rekening Sekarang
                            </button>
                            <a href="/simpanan/{{ $nia->id }}" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#konfirmasi').on('change', function() {
        $('#btnKonfirmasiTutup').prop('disabled', !this.checked);
    });

    $('#formTutupRekening').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Tutup Rekening?',
            text: 'Rekening akan ditutup permanen dan saldo akan ditarik sesuai rincian di atas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tutup Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mohon menunggu...',
                text: 'Sedang memproses penutupan rekening...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            window.location.href = res.redirect || '/simpanan';
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan.' });
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Terjadi kesalahan: ' + xhr.statusText;
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        });
    });
</script>
@endsection