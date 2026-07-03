@extends('layouts.base')
@section('content')
<div class="app-main__inner">       
    <div class="tab-pane fade show active" id="ProfilDebitur" role="tabpanel" aria-labelledby="ProfilDebitur">
        <div class="main-card mb-3 card">
            <div class="card-body p-3">
                <div class="widget-content mb-2">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="widget-heading">Nasabah {{ $nia->anggota->namadepan }} CIF. {{$nia->id}} 
                                ({{ $nia->js->nama_js }})</div>
                            <div class="widget-subheading">
                                <span class="badge bg-info">{{ $nia->anggota->nia }}</span>
                                <span class="badge bg-info">{{ $nia->anggota->alamat_anggota }}</span>
                                <span class="badge bg-info">
                                    {{ $nia->anggota->d->sebutan_desa->sebutan_desa }}
                                    {{ $nia->anggota->d->nama_desa }}
                                </span>
                            </div>
                        </div>
                        <div class="widget-content-right">
                            <button class="btn btn-secondary btn-sm" type="button" onclick="window.location.href='/simpanan'">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-1 align-items-center">
                    <button class="btn btn-danger btn-sm" onclick="generateSimpanan({{ $nia->id }})" type="button">
                        <i class="fa fa-refresh"></i> Generate Simpanan
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="window.open('/cetak_formulir/{{ $nia->id }}')" type="button">
                        <i class="fa fa-pencil-square"></i> Cetak Form Pendaftaran
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="window.open('/cetak_kop/{{ $nia->id }}')" type="button">
                        <i class="fa fa-print"></i> Cetak KOP Buku
                    </button>
                    <button class="btn btn-success btn-sm" onclick="window.open('/cetak_sertifikat/{{ $nia->id }}')" type="button">
                        <i class="fa fa-address-card"></i> Cetak Sertifikat Deposito
                    </button>
                    <button class="btn btn-info btn-sm" onclick="window.open('/simpanan/surat-kuasa/{{ $nia->id }}')" type="button">
                        <i class="fa fa-file-text"></i> Surat Kuasa Simpanan Tabungan
                    </button>
                </div>
            </div>
        </div>
        <div class="tab-content">  
            <div class="row">
                <div class="col-md-3">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <form class="">
                                <div class="position-relative mb-3">
                                    <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                                    <input autocomplete="off" type="text" name="nomor_rekening" id="nomor_rekening" class="form-control" value="{{$nia->nomor_rekening}}" disabled>
                                    <small class="text-danger" id="msg_nomor_rekening"></small>
                                </div>
                                <div class="position-relative mb-3">
                                    <label for="nama_debitur" class="form-label">Nama Debitur</label>
                                    <input autocomplete="off" type="text" name="nama_debitur" id="nama_debitur" class="form-control" value="{{$nia->anggota->namadepan}}" disabled>
                                    <small class="text-danger" id="msg_nama_debitur"></small>
                                </div>
                                <div class="position-relative mb-3">
                                    <label for="nik" class="form-label">NIK</label>
                                    <input autocomplete="off" type="text" name="nik" id="nik" class="form-control" value="{{$nia->anggota->nik}}" disabled>
                                    <small class="text-danger" id="msg_nik"></small>
                                </div>
                                
                                <div class="input-group input-group-static my-3">
                                    <label>TRANSAKSI SIMPANAN</label>
                                </div>
                                <div class="position-relative mb-3">
                                    <label for="tgl_transaksi" class="form-label">Tgl Transaksi</label>
                                    <input autocomplete="off" type="text" name="tgl_transaksi" id="tgl_transaksi" class="form-control date" value="{{ date('d/m/Y') }}">
                                    <small class="text-danger" id="msg_tgl_transaksi"></small>
                                </div>
                                <div class="position-relative mb-3">
                                    <label for="tarik_tunai" class="form-label">Jenis Mutasi</label>
                                    <div class="row">
                                        <div class="col-xs-1">
                                            <div class="radio radio-success">
                                                <input type="radio" name="jenis_mutasi" id="setor_tunai" value="1">
                                                <label for="setor_tunai">Setor Tunai</label>
                                            </div>
                                        </div>
                                        <div class="col-xs-1">
                                            <div class="radio radio-danger">
                                                <input type="radio" name="jenis_mutasi" id="tarik_tunai" value="2">
                                                <label for="tarik_tunai">Tarik Tunai</label>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-danger" id="msg_jenis_mutasi"></small>
                                </div>
                                    <div class="position-relative mb-3">
                                    <label for="jumlah" class="form-label">Jumlah (Rp.)</label>
                                    <input autocomplete="off" type="text" name="jumlah" id="jumlah" class="form-control" value="">
                                    <small class="text-danger" id="msg_jumlah"></small>
                                </div>
                                
                                    <button id="simpanTransaksi" class="btn btn-primary btn-sm float-end ms-2" type="button">
                                    Simpan Transaksi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <style>
                                .forminline {
                                    display: flex;
                                    gap: 10px;
                                    align-items: center;
                                    display: flex;
                                    justify-content: flex-end; /* Align items to the right */
                                
                                }
                                .formcontrol {
                                    width: 150px; /* Set the same width for both select elements */
                                    padding-left: 10px;
                                    padding-right: 20px;
                                    appearance: none;
                                    -webkit-appearance: none;
                                    -moz-appearance: none;
                                    border: 1px solid #ccc;
                                }
                                
                                /* Custom Radio Button Styling */
                                .radio-success input[type="radio"],
                                .radio-danger input[type="radio"] {
                                    width: 18px;
                                    height: 18px;
                                    cursor: pointer;
                                    margin-right: 8px;
                                    vertical-align: middle;
                                }
                                
                                /* Untuk browser modern */
                                .radio-success input[type="radio"] {
                                    accent-color: #0d6efd;
                                }
                                
                                .radio-danger input[type="radio"] {
                                    accent-color: #dc3545;
                                }
                                
                                /* Custom styling dengan appearance none untuk lebih konsisten */
                                .radio-success input[type="radio"],
                                .radio-danger input[type="radio"] {
                                    -webkit-appearance: none;
                                    -moz-appearance: none;
                                    appearance: none;
                                    border: 2px solid #6c757d;
                                    border-radius: 50%;
                                    outline: none;
                                    transition: all 0.3s;
                                }
                                
                                .radio-success input[type="radio"]:checked {
                                    border-color: #0d6efd;
                                    background-color: #0d6efd;
                                    box-shadow: inset 0 0 0 3px white;
                                }
                                
                                .radio-danger input[type="radio"]:checked {
                                    border-color: #dc3545;
                                    background-color: #dc3545;
                                    box-shadow: inset 0 0 0 3px white;
                                }
                                
                                .radio-success input[type="radio"]:hover,
                                .radio-danger input[type="radio"]:hover {
                                    border-width: 3px;
                                }
                                
                                .radio-success label,
                                .radio-danger label {
                                    cursor: pointer;
                                    vertical-align: middle;
                                    user-select: none;
                                }
                                
                                /* Styling untuk input jumlah saat tarik tunai */
                                #jumlah.tarik-tunai {
                                    border-color: #dc3545;
                                    color: #dc3545;
                                }
                                
                                #jumlah.tarik-tunai:focus {
                                    border-color: #dc3545;
                                    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
                                }
                            </style>
                            <form class="forminline">
                                <select id="bulants" name="bulants" class="js-example-basic-single formcontrol">
                                    <option value="0">
                                        Semua Bulan
                                    </option>
                                    @foreach(range(1, 12) as $bulan)
                                        <option value="{{ $bulan }}" {{ date('n') == $bulan ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $bulan, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <select id="tahunts" name="tahunts" class="js-example-basic-single formcontrol">
                                    <option value="0">
                                        Semua Tahun
                                    </option>
                                    @foreach(range(date('Y')-5, date('Y')+5) as $tahun)
                                        <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                                            {{ $tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>                          
                        </div>
                        <div class="divider mt-0" style="margin-bottom: 10px;"></div>
                        <div class="card-body pb-2">
                            <div id="transaksi-container">
                                <!-- Tabel transaksi akan dimuat di sini -->
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
    $('.js-example-basic-single').select2({
      theme: 'bootstrap-5'
    });

    $("#jumlah").maskMoney({
            allowNegative: true
        });

    $('.date').datepicker({
        dateFormat: 'dd/mm/yy'
    });

$(document).ready(function() {
    var currentDate = new Date();
    var currentMonth = currentDate.getMonth() + 1;
    var currentYear = currentDate.getFullYear();

    tableTransaksi(currentMonth, currentYear);
    
    // Toggle class pada input jumlah berdasarkan jenis mutasi
    $('input[name="jenis_mutasi"]').change(function() {
        var jenisMutasi = $(this).val();
        if (jenisMutasi == '2') { // Tarik tunai
            $('#jumlah').addClass('tarik-tunai');
        } else { // Setor tunai
            $('#jumlah').removeClass('tarik-tunai');
        }
    });

    function tableTransaksi(bulan, tahun) {
        $.get('/simpanan/get-transaksi', {
            cif: '{{ $nia->id }}',
            bulan: bulan,
            tahun: tahun
        }, function(result) {
            $('#transaksi-container').html(result);
        }).fail(function(xhr, status, error) {
            console.error("Error loading transactions:", error);
            $('#transaksi-container').html('<p>Error loading transactions. Please try again.</p>');
        });
    }

    $('#bulants, #tahunts').change(function() {
        var bulan = $('#bulants').val();
        var tahun = $('#tahunts').val();
        tableTransaksi(bulan, tahun);
    });

    $('#simpanTransaksi').click(function() {
        var jenisMutasi = $('input[name="jenis_mutasi"]:checked').val();
        var tglTransaksi = $('#tgl_transaksi').val();
        var jumlah = $('#jumlah').val();
        var nomorRekening = $('#nomor_rekening').val();
        var namaDebitur = $('#nama_debitur').val();
        var nia = '{{ $nia->id }}';

        if (!jenisMutasi || !tglTransaksi || !jumlah) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Mohon lengkapi semua field.',
            });
            return;
        }

        Swal.fire({
            title: 'Mohon menunggu',
            text: 'Sedang memproses transaksi...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            url: '/simpanan/simpan-transaksi',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                jenis_mutasi: jenisMutasi,
                tgl_transaksi: tglTransaksi,
                jumlah: jumlah,
                nomor_rekening: nomorRekening,
                nama_debitur: namaDebitur,
                nia: nia
            },
            success: function(response) {
                if (response.success) {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Transaksi ' + (jenisMutasi == '1' ? 'setor' : 'tarik') + ' berhasil disimpan',
                        confirmButtonText: 'Oke'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            refreshTransaksiContainer();
                            resetForm();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan transaksi: ' + response.message,
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan: ' + error,
                });
            }
        });
    });

    function refreshTransaksiContainer() {
        var bulan = $('#bulan').val();
        var tahun = $('#tahun').val();
        tableTransaksi(bulan, tahun);
    }

    function resetForm() {
        $('input[name="jenis_mutasi"]').prop('checked', false);
        $('#tgl_transaksi').val('');
        $('#jumlah').val('').removeClass('tarik-tunai');
    }
});

function generateSimpanan(cif) {
    Swal.fire({
        title: 'Generate Real Simpanan?',
        text: 'Proses ini akan menghapus dan membangun ulang data real simpanan untuk CIF ' + cif + '. Lanjutkan?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Generate!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon menunggu...',
                text: 'Sedang memproses generate real simpanan...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '/simpanan/generate/' + cif,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Generate real simpanan berhasil.',
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            var bulan = $('#bulants').val();
                            var tahun = $('#tahunts').val();
                            tableTransaksi(bulan, tahun);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Generate gagal.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan: ' + (xhr.responseJSON?.message || xhr.statusText)
                    });
                }
            });
        }
    });
}
</script>
@endsection
