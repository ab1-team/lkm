@extends('layouts.base')

@section('content')

<div class="app-main__inner">   
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="fa fa-bank"></i>
                </div>
                <div><b>Kalkulasi Perhitungan Bunga dan Biaya</b>
                    <div class="page-title-subheading">
                         {{ Session::get('nama_lembaga') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <form method="POST">
                            @csrf
                            <div class="row">
                                <!-- CIF -->
                                <div class="col-md-4">
                                    
                                    <div class="form-group">
                                        <label for="bulants">Bulan</label>
                                        <select id="bulants" name="bulants" class="form-control">
                                            <option value="0">
                                                Semua Bulan
                                            </option>
                                            @foreach(range(1, 12) as $bulan)
                                                <option value="{{ $bulan }}" {{ date('n') == $bulan ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $bulan, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="tahunts">Tahun</label>
                                        <select id="tahunts" name="tahunts" class="form-control">
                                            <option value="0">
                                                Semua Tahun
                                            </option>
                                            @foreach(range(date('Y')-5, date('Y')+5) as $tahun)
                                                <option value="{{ $tahun }}" {{ date('Y') == $tahun ? 'selected' : '' }}>
                                                    {{ $tahun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="cif">CIF</label>
                                        <input type="text" name="cif" id="cif" class="form-control" placeholder="Semua CIF">
                                    </div>

                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-info d-flex " role="alert" style="background-color: #e7f3fe; color: #31708f; border: 1px solid #bce8f1;">
                                        <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-right: 10px; margin-top: 10px; vertical-align:top"></i>
                                        <div id="info-bunga">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button id="simpanBunga"  type="button" class="btn btn-primary mt-3">Proses Kalkulasi</button>
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
        

        $(document).ready(function() {
            var currentMonth = $('#bulants').val();
            var currentYear = $('#tahunts').val();

            tableTransaksi(currentMonth, currentYear);

            function tableTransaksi(bulan, tahun) {
                $.get('/bunga/info', {
                    bulan: bulan,
                    tahun: tahun
                }, function(result) {
                    $('#info-bunga').html(result);
                }).fail(function(xhr, status, error) {
                    console.error("Error loading :", error);
                    $('#info-bunga').html('<p>Error loading. Please try again.</p>');
                });
            }

            $('#bulants, #tahunts').change(function() {
                var bulan = $('#bulants').val();
                var tahun = $('#tahunts').val();
                tableTransaksi(bulan, tahun);
            });

        });

        let childWindow, loading;
        let prosesSedangBerjalan = false; // guard anti double-click / popup ganda

        $(document).on('click', '#simpanBunga', function(e) {
            e.preventDefault();

            // Cegah klik ganda / popup kedua untuk proses yang sama
            if (prosesSedangBerjalan) {
                return;
            }
            prosesSedangBerjalan = true;
            $(this).prop('disabled', true).addClass('disabled');

            var bulan = $('select#bulants').val();
            var tahun = $('select#tahunts').val();
            var cif = $('#cif').val().trim(); // ambil input dari #cif

            loading = Swal.fire({
                title: "Mohon Menunggu..",
                html: "Proses Hitung bunga",
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Susun URL dengan parameter yang sudah diencode
            var url = '/simpan_bunga?bulan=' + encodeURIComponent(bulan) +
                        '&tahun=' + encodeURIComponent(tahun) +
                        '&start=0' +
                        '&id=' + encodeURIComponent(cif);

            childWindow = window.open(url, '_blank');

            // Popup diblokir browser: proses tidak dijalankan, lepaskan tombol
            if (!childWindow) {
                prosesSedangBerjalan = false;
                $('#simpanBunga').prop('disabled', false).removeClass('disabled');
                if (loading) { loading.close(); }
                Swal.fire({
                    title: "Popup diblokir",
                    html: "Izinkan popup untuk window ini, lalu tekan Proses Kalkulasi kembali.",
                    icon: "warning"
                });
            }
        });

        window.addEventListener('message', function(event) {
            if (event.data === 'closed') {
                if (loading) { loading.close(); }
                window.location.reload()
            }
        })
    </script>
@endsection
