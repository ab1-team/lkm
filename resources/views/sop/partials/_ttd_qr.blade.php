@php
    $currentTtd = \App\Utils\QrTtdHelper::publicUrl($kec->id);
    $preview    = $currentTtd ?: '/assets/img/qr.png';
    $hasFile    = $currentTtd !== null;
    $withName   = \App\Utils\QrTtdHelper::hasNameSuffix($kec->id);
@endphp

<form action="/pengaturan/ttd-qr/save/{{ $kec->id }}" method="post" enctype="multipart/form-data" id="FormTtdQr">
    @csrf

    <div class="text-center py-4">
        <div class="position-relative d-inline-block mb-4 logo-upload-container" id="EditTtdQrDropzone" style="cursor: pointer; transition: all 0.3s ease;">
            <div class="logo-preview-wrapper p-3 bg-white shadow-sm d-flex align-items-center justify-content-center position-relative"
                 style="width: 180px; height: 180px; border-radius: 12px; border: 3px dashed {{ $hasFile ? '#22c55e' : '#cbd5e1' }}; transition: all 0.3s ease; overflow: hidden; margin: 0 auto;">
                <img src="{{ $preview }}" alt="Gambar Tanda Tangan Lembaga" id="previewTtdQr"
                    class="img-fluid"
                    onerror="this.onerror=null; this.src='/assets/img/qr.png';"
                    style="max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.3s ease;">

                <div class="logo-hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white"
                     style="background: rgba(15, 23, 42, 0.75); opacity: 0; transition: all 0.3s ease; border-radius: 12px;">
                    <i class="fa fa-signature fa-2x mb-2" style="transform: scale(0.8); transition: all 0.3s ease;"></i>
                    <span class="text-xs font-weight-bold">{{ $hasFile ? 'Klik atau jatuhkan file untuk mengganti' : 'Klik atau jatuhkan file untuk mengunggah' }}</span>
                </div>
            </div>
        </div>

        <div class="mt-2">
            <p class="text-xs text-muted mb-0" style="color: #64748b; font-size: 0.75rem;">
                <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                Format didukung: <strong>JPG, JPEG, PNG</strong> (Maks. 4MB).
            </p>
            <p class="text-xs text-muted mb-0 mt-1" id="pathInfoTtd" style="color: #64748b; font-size: 0.75rem;">
                @if ($hasFile)
                    Gambar tanda tangan saat ini tersimpan untuk lokasi ini.
                @else
                    Pilih gambar untuk dipratinjau. Klik <strong>Simpan Perubahan</strong> untuk mengunggah.
                @endif
            </p>
            <p class="text-xs text-warning mb-0 mt-1 d-none" id="pendingFileInfo" style="font-size: 0.78rem;">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                <span>Gambar baru dipilih, klik <strong>Simpan Perubahan</strong> untuk mengunggah.</span>
            </p>
        </div>
    </div>

    <input type="file" name="gambar_ttd" id="gambar_ttd" class="d-none" accept="image/jpeg,image/jpg,image/png">

    <div class="d-flex justify-content-center align-items-center mb-3">
        <div class="form-check form-switch d-inline-flex align-items-center" style="padding-left: 2.5rem;">
            <input type="checkbox"
                   role="switch"
                   name="dengan_nama"
                   id="dengan_nama"
                   value="1"
                   class="form-check-input"
                   {{ ($withName || !$hasFile) ? 'checked' : '' }}
                   style="cursor: pointer;">
            <label class="form-check-label ms-2" for="dengan_nama" style="font-size: 0.9rem; cursor: pointer;">
                Sertakan nama penandatangan di bawah gambar
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-end align-items-center mt-4 flex-wrap gap-2">
        <button type="button" id="HapusTtdQr" class="btn btn-sm btn-outline-danger mb-0"
                {{ !$hasFile ? 'disabled' : '' }}
                title="{{ $hasFile ? 'Hapus gambar' : 'Tidak ada gambar untuk dihapus' }}">
            <i class="fa-solid fa-trash me-1"></i> Hapus Gambar
        </button>
        <button type="button" id="SimpanTtdQr" data-target="#FormTtdQr"
            class="btn btn-sm btn-dark mb-0 btn-simpan">
            Simpan Perubahan
        </button>
    </div>
</form>

<style>
    .logo-upload-container:hover .logo-preview-wrapper {
        border-color: #3b82f6 !important;
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 122, 0.25), 0 8px 10px -6px rgba(59, 130, 122, 0.2) !important;
    }
    .logo-upload-container:hover .logo-hover-overlay {
        opacity: 1 !important;
    }
    .logo-upload-container:hover .logo-hover-overlay i {
        transform: scale(1) !important;
    }
    .logo-upload-container.dragover .logo-preview-wrapper {
        border-color: #22c55e !important;
        background: #f0fdf4 !important;
        transform: translateY(-2px);
    }
</style>
