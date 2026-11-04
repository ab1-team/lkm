@extends('layouts.base')
@section('content')
    <style>
        .select2-container .select2-selection--single .select2-selection__rendered {
            font-size: 14px;
            /* Default font size */
        }

        .nav-pills {
            background-color: #ffffff;
            /* Warna background */
            border-radius: 8px;
            /* Sudut melengkung */
            padding: 5px;
            /* Padding agar lebih rapi */
        }

        .nav-pills .nav-link {
            color: #333;
            /* Warna teks */
        }

        .nav-pills .nav-link.active {
            background-color: #007bff;
            /* Warna background tab aktif */
            color: rgb(253, 253, 253);
            /* Warna teks tab aktif */
            border-radius: 5px;
            /* Sudut melengkung */
        }
    </style>
    <div class="app-main__inner">
        <div class="card-body">

            <div class="nav-wrapper position-relative end-0">
                <ul class="nav nav-pills nav-fill p-1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#ProfilKelompok" role="tab"
                            aria-controls="ProfilKelompok" aria-selected="true">&nbsp;&nbsp;
                            <i class="fa-solid fa fa-users"></i> &nbsp;&nbsp;
                            Profil Kelompok
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#RiwayatPiutang" role="tab"
                            aria-controls="RiwayatPiutang" aria-selected="false">&nbsp;&nbsp;
                            <i class="fa-solid fa fa-history"></i> &nbsp;&nbsp;
                            Riwayat Piutang
                        </a>
                    </li>
                </ul>
                <div class="tab-content mt-2">
                    <div class="tab-pane fade show active" id="ProfilKelompok" role="tabpanel"
                        aria-labelledby="ProfilKelompok">
                        <div class="card">
                            <div class="card-body">
                                <form action="/database/kelompok/{{ $kelompok->kd_kelompok }}" method="post" id="Kelompok">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_kd_kelompok" id="_kd_kelompok" value="{{ $kelompok->kd_kelompok }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="kode_kelompok">Kode Kelompok</label>
                                                <input autocomplete="off" type="text" name="kode_kelompok"
                                                    id="kode_kelompok" class="form-control" value="{{ $kelompok->kd_kelompok }}" readonly>
                                                <small class="text-danger" id="msg_kode_kelompok"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="nama_kelompok">Nama Kelompok</label>
                                                <input autocomplete="off" type="text" name="nama_kelompok"
                                                    id="nama_kelompok" class="form-control"
                                                    value="{{ $kelompok->nama_kelompok }}">
                                                <small class="text-danger" id="msg_nama_kelompok"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="desa">Desa/Kelurahan</label>
                                                <select class="detailselect2 form-control" name="desa" id="desa">
                                                    <option value="">Pilih Desa/Kelurahan</option>
                                                    @foreach ($desa as $ds)
                                                        <option value="{{ $ds->kd_desa }}"
                                                            {{ old('desa', $desa_dipilih) == $ds->kd_desa ? 'selected' : '' }}>
                                                            {{ $ds->sebutan_desa->sebutan_desa }}
                                                            {{ $ds->nama_desa }} - {{ $ds->nama_kec }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger" id="msg_desa"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="alamat_kelompok">Alamat Kelompok</label>
                                                <input autocomplete="off" type="text" name="alamat_kelompok"
                                                    id="alamat_kelompok" class="form-control"
                                                    value="{{ $kelompok->alamat_kelompok }}">
                                                <small class="text-danger" id="msg_alamat_kelompok"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="telpon">No. Telpon</label>
                                                <input autocomplete="off" type="text" name="telpon"
                                                    id="telpon" class="form-control"
                                                    value="{{ $kelompok->telpon }}">
                                                <small class="text-danger" id="msg_telpon"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="jenis_kegiatan">Jenis Kegiatan</label>
                                                <select class="detailselect2 form-control" name="jenis_kegiatan" id="jenis_kegiatan">
                                                    <option value="">Pilih Jenis Kegiatan</option>
                                                    @foreach ($jenis_kegiatan as $jk)
                                                        <option value="{{ $jk->id }}"
                                                            {{ optional($kelompok->kegiatan)->id == $jk->id ? 'selected' : '' }}>
                                                            {{ $jk->nama_jk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger" id="msg_jenis_kegiatan"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="jenis_usaha">Jenis Usaha</label>
                                                <select class="detailselect2 form-control" name="jenis_usaha" id="jenis_usaha">
                                                    <option value="">Pilih Jenis Usaha</option>
                                                    @foreach ($jenis_usaha as $ju)
                                                        <option value="{{ $ju->id }}"
                                                            {{ optional($kelompok->usaha)->id == $ju->id ? 'selected' : '' }}>
                                                            {{ $ju->nama_ju }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger" id="msg_jenis_usaha"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="tingkat_kelompok">Tingkat Kelompok</label>
                                                <select class="detailselect2 form-control" name="tingkat_kelompok" id="tingkat_kelompok">
                                                    <option value="">Pilih Tingkat Kelompok</option>
                                                    @foreach ($tingkat_kelompok as $tk)
                                                        <option value="{{ $tk->id }}"
                                                            {{ optional($kelompok->tk)->id == $tk->id ? 'selected' : '' }}>
                                                            {{ $tk->nama_tk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger" id="msg_tingkat_kelompok"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="fungsi_kelompok">Fungsi Kelompok</label>
                                                <select class="detailselect2 form-control" name="fungsi_kelompok" id="fungsi_kelompok">
                                                    <option value="">Pilih Fungsi Kelompok</option>
                                                    @foreach ($fungsi_kelompok as $fk)
                                                        <option value="{{ $fk->id }}"
                                                            {{ optional($kelompok->fk)->id == $fk->id ? 'selected' : '' }}>
                                                            {{ $fk->nama_fk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger" id="msg_fungsi_kelompok"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="tgl_berdiri">Tanggal Berdiri</label>
                                                <input autocomplete="off" type="text" name="tgl_berdiri" id="tgl_berdiri"
                                                    class="form-control date" value="{{ $kelompok->tgl_berdiri }}">
                                                <small class="text-danger" id="msg_tgl_berdiri"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="ketua">Nama Koordinator</label>
                                                <input autocomplete="off" type="text" name="ketua"
                                                    id="ketua" class="form-control"
                                                    value="{{ $kelompok->ketua }}">
                                                <small class="text-danger" id="msg_ketua"></small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="sekretaris" id="sekretaris"
                                            value="{{ $kelompok->sekretaris }}">
                                    </div>
                                    <div class="row">
                                        <input type="hidden" name="bendahara" id="bendahara"
                                            value="{{ $kelompok->bendahara }}">
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="jenis_produk_pinjaman">Jenis Produk Pinjaman</label>
                                                <select class="detailselect2 form-control" name="jenis_produk_pinjaman" id="jenis_produk_pinjaman">
                                                    <option value="">Pilih Jenis Produk Pinjaman</option>
                                                    @foreach ($jenis_produk_pinjaman as $jpp)
                                                        <option value="{{ $jpp->id }}"
                                                            {{ optional($kelompok->jpp)->id == $jpp->id ? 'selected' : '' }}>
                                                            {{ $jpp->nama_jpp }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger" id="msg_jenis_produk_pinjaman"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="position-relative mb-3">
                                                <label for="jumlah_anggota">Jumlah Anggota</label>
                                                <input autocomplete="off" type="number" name="jumlah_anggota"
                                                    id="jumlah_anggota" class="form-control"
                                                    value="{{ $kelompok->jumlah_anggota }}">
                                                <small class="text-danger" id="msg_jumlah_anggota"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-github btn-sm float-end btn btn-sm btn-dark mb-0"
                                        id="SimpanKelompok">
                                        Simpan Kelompok
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm me-3 float-end"
                                        id="BlokirKelompok">
                                        @php
                                            $status = '0';
                                            if ($kelompok->status == '0') {
                                                $status = '1';
                                            }
                                        @endphp
                                        @if ($status == '0')
                                            Blokir Kelompok
                                        @else
                                            Lepaskan Blokiran
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="RiwayatPiutang" role="tabpanel" aria-labelledby="RiwayatPiutang">
                        <div class="card bg-gradient-default">
                            <div class="card-body">
                                <h5 class="font-weight-normal text-info text-gradient">
                                    Riwayat Piutang {{ ucwords($kelompok->nama_kelompok) }}
                                </h5>
                                <ul class="list-group list-group-flush mt-2">
                                    @foreach ($kelompok->pinkel as $pinkel)
                                        <li class="list-group-item">
                                            @php
                                                if ($pinkel->status == 'P') {
                                                    $tgl = $pinkel->tgl_proposal;
                                                    $jumlah = $pinkel->proposal;
                                                } elseif ($pinkel->status == 'V') {
                                                    $tgl = $pinkel->tgl_verifikasi;
                                                    $jumlah = $pinkel->verifikasi;
                                                } elseif ($pinkel->status == 'W') {
                                                    $tgl = $pinkel->tgl_cair;
                                                    $jumlah = $pinkel->alokasi;
                                                } else {
                                                    $tgl = $pinkel->tgl_cair;
                                                    $jumlah = $pinkel->alokasi;
                                                }
                                            @endphp
                                            <blockquote data-link="/detail/{{ $pinkel->id }}"
                                                class="blockquote text-black mb-1 pointer">
                                                <p class="text-dark ms-3">
                                                    <span class="badge bg-{{ $pinkel->sts->warna_status }}">
                                                        Loan ID. {{ $pinkel->id }}
                                                        {{ $kelompok->nama_kelompok }}
                                                    </span>
                                                    |
                                                    <span>
                                                        {{ Tanggal::tglIndo($tgl) }}
                                                    </span>
                                                    |
                                                    <span>
                                                        {{ number_format($jumlah) }}
                                                    </span>
                                                    |
                                                    <span>
                                                        {{ $pinkel->pros_jasa / $pinkel->jangka }}% @
                                                        {{ $pinkel->jangka }} Bulan
                                                        --
                                                        {{ $pinkel->angsuran_pokok->nama_sistem }}
                                                    </span>
                                                    |
                                                    <span>
                                                        {{ $pinkel->sts->nama_status }}
                                                    </span>

                                                </p>
                                            </blockquote>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body p-2">
                        <a href="/database/kelompok" class="btn btn-info btn-sm float-end mb-0">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br><br><br><br>
    <form action="/database/kelompok/{{ $kelompok->kd_kelompok }}/blokir" method="post" id="Blokir">
        @csrf
        @php
            $status = '0';
            if ($kelompok->status == '0') {
                $status = '1';
            }
        @endphp
        <input type="hidden" name="status" id="status" value="{{ $status }}">
    </form>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.detailselect2').select2({
                theme: 'bootstrap4',
            });
        });

        // Function to set font size
        function setFontSize(size) {
            $('.select2-container .select2-selection--single .select2-selection__rendered').css('font-size', size + 'px');
        }
        $('.date').datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $(document).on('click', '#SimpanKelompok', function(e) {
            e.preventDefault()
            $('small').html('')
            var form = $('#Kelompok')
            $.ajax({
                type: 'post',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    Swal.fire('Berhasil', result.msg, 'success')
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static').addClass(
                            'is-invalid')
                        $('#msg_' + key).html(res)
                    })
                }
            })
        })
        $(document).on('click', '#BlokirKelompok', function(e) {
            e.preventDefault()
            let blokir = $('#Blokir #status').val()
            let title = 'Blokir Kelompok?'
            let text = 'Dengan klik Ya maka kelompok ini tidak akan bisa mengajukan pinjaman lagi. Yakin?'
            if (blokir != '0') {
                title = 'Lepaskan Blokiran?'
                text = 'Dengan klik Ya maka kelompok ini akan dilepas dari blokirannya. Yakin lepaskan?'
            }
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((res) => {
                if (res.isConfirmed) {
                    var form = $('#Blokir')
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(result) {
                            if (result.success) {
                                Swal.fire({
                                    title: 'Berhasil',
                                    text: result.msg,
                                    icon: 'success',
                                }).then(() => {
                                    window.location.reload();
                                })
                            }
                        }
                    })
                }
            })
        })
        $(document).on('click', '.blockquote', function(e) {
            e.preventDefault()
            var link = $(this).attr('data-link')
            window.location.href = link
        })
    </script>
@endsection
