@php
    use App\Utils\Pinjaman;
    $currentFormat = $kec->spk_format ?? '';
    $previewResult = Pinjaman::renderSpkPreview($kec, $currentFormat);
    $exampleResult = Pinjaman::renderSpkPreview($kec, '{no_urut}/SPK/{lembaga_short}/{bulanromawi}/{tahun}');
@endphp

<form action="/pengaturan/spk_format/{{ $kec->id }}" method="post" id="FormSpkFormat">
    @csrf
    @method('PUT')
    <div class="position-relative mb-3">
        <label for="spk_format" class="form-label">Format Penomoran SPK</label>
        <input autocomplete="off" type="text" name="spk_format" id="spk_format"
            class="form-control" maxlength="255"
            placeholder="Contoh: {no_urut}/SPK/{lembaga_short}/{bulanromawi}/{tahun}"
            value="{{ $currentFormat }}">
        <small class="text-muted">
            Format ini digunakan untuk mengisi otomatis kolom <b>Nomor SPK</b> saat status pinjaman berpindah dari
            <b>Verifikasi (V)</b> ke <b>Waiting (W)</b>. Jika dikosongkan, Nomor SPK tidak akan diisi otomatis.
        </small>
        <small class="text-danger d-block" id="msg_spk_format"></small>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert  mb-0 py-2">
                <small><b>Hasil Pratinjau Saat Ini:</b> <br> <code id="spk_format_preview">{{ $previewResult }}</code></small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert  mb-2 py-2">
                <small><b>Contoh Input:</b> <br> <code>{no_urut}/SPK/{lembaga_short}/{bulanromawi}/{tahun}</code></small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert  mb-0 py-2">
                <small><b>Hasil Contoh:</b> <br><code>{{ $exampleResult }}</code></small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="button" id="SimpanSpkFormat" data-target="#FormSpkFormat"
            class="btn btn-sm btn-dark mb-0 btn-simpan">
            Simpan Perubahan
        </button>
    </div>
</form>

<div class="main-card mb-3 card mt-4">
    <div class="card-body">
        <h6 class="card-title">
            <i class="fa-solid fa-key"></i> Kata Kunci
        </h6>
        <p class="text-muted small mb-2">
            Kata kunci berikut dapat digunakan di dalam format penomoran SPK dan akan diganti secara otomatis
            dengan nilai yang sesuai ketika SPK diterbitkan.
        </p>
        <div class="table-responsive">
            <table class="table table-striped table-sm mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th width="10">No</th>
                        <th width="180">Kata Kunci</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01.</td>
                        <td><code>{no_urut}</code></td>
                        <td>Nomor urut proposal tahun berjalan.</td>
                    </tr>
                    <tr>
                        <td>02.</td>
                        <td><code>{kec_id}</code></td>
                        <td>ID Kecamatan (Kode lokasi Lembaga).</td>
                    </tr>
                    <tr>
                        <td>03.</td>
                        <td><code>{kd_kec}</code></td>
                        <td>Kode Kecamatan (sesuai master wilayah).</td>
                    </tr>
                    <tr>
                        <td>04.</td>
                        <td><code>{nama_kec}</code></td>
                        <td>Nama Kecamatan.</td>
                    </tr>
                    <tr>
                        <td>05.</td>
                        <td><code>{lembaga_short}</code></td>
                        <td>Nama singkat Lembaga.</td>
                    </tr>
                    <tr>
                        <td>06.</td>
                        <td><code>{lembaga_long}</code></td>
                        <td>Nama lengkap Lembaga.</td>
                    </tr>
                    <tr>
                        <td>07.</td>
                        <td><code>{tahun}</code></td>
                        <td>Tahun pencairan (4 digit, mis. 2026).</td>
                    </tr>
                    <tr>
                        <td>08.</td>
                        <td><code>{bulan}</code></td>
                        <td>Bulan pencairan (2 digit, mis. 09).</td>
                    </tr>
                    <tr>
                        <td>09.</td>
                        <td><code>{bulanromawi}</code></td>
                        <td>Bulan pencairan dalam angka Romawi (mis. IX).</td>
                    </tr>
                    <tr>
                        <td>10.</td>
                        <td><code>{loan_id}</code></td>
                        <td>Loan ID (ID Pinjaman).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        var $input = $('#spk_format');
        var $preview = $('#spk_format_preview');
        var exampleFormat = '{no_urut}/SPK/{lembaga_short}/{bulanromawi}/{tahun}';
        var exampleResult = @json($exampleResult);
        var originalSaved = @json($currentFormat);
        var previewUrl = '/pengaturan/spk_format/preview';

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        $input.on('input', function () {
            var val = $(this).val();
            if (val === originalSaved) {
                $preview.text(@json($previewResult));
                return;
            }
            if (!val) {
                $preview.text('(kosong)');
                return;
            }
            $.ajax({
                type: 'GET',
                url: previewUrl,
                data: { format: val },
                success: function (res) {
                    if (res && res.preview !== undefined) {
                        $preview.text(res.preview || '(kosong)');
                    }
                },
                error: function () {
                    $preview.text(exampleResult);
                }
            });
        });

        $(document).off('click', '#SimpanSpkFormat').on('click', '#SimpanSpkFormat', function (e) {
            e.preventDefault();
            var form = $('#FormSpkFormat');
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function (result) {
                    if (result.success) {
                        Toastr('success', result.msg);
                        originalSaved = $input.val();
                    } else if (result.msg) {
                        Toastr('error', result.msg);
                    }
                },
                error: function (result) {
                    var respons = result.responseJSON;
                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error');
                    if (respons) {
                        $.map(respons, function (res, key) {
                            $('#msg_' + key).html(res);
                        });
                    }
                }
            });
        });
    })();
</script>
