<div class="text-center py-4">
    <!-- Logo Preview Container -->
    <div class="position-relative d-inline-block mb-4 logo-upload-container" id="EditLogo" style="cursor: pointer; transition: all 0.3s ease;">
        <div class="logo-preview-wrapper p-3 bg-white shadow-sm d-flex align-items-center justify-content-center position-relative" 
             style="width: 180px; height: 180px; border-radius: 12px; border: 3px dashed #cbd5e1; transition: all 0.3s ease; overflow: hidden; margin: 0 auto;">
            <img src="{{ asset('storage/logo/' . Session::get('logo')) }}" alt="Logo Lembaga"
                class="img-fluid" id="previewLogo" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.3s ease;">
            
            <!-- Hover Overlay -->
            <div class="logo-hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white"
                 style="background: rgba(15, 23, 42, 0.75); opacity: 0; transition: all 0.3s ease; border-radius: 12px;">
                <i class="fa fa-camera fa-2x mb-2" style="transform: scale(0.8); transition: all 0.3s ease;"></i>
                <span class="text-xs font-weight-bold">Ubah Logo</span>
            </div>
        </div>
    </div>

    <!-- Guidance & Button -->
    <div class="mt-2">
        <p class="text-xs text-muted mb-0" style="color: #64748b; font-size: 0.75rem;">
            <i class="fa-solid fa-circle-info me-1 text-primary"></i> 
            Format didukung: <strong>JPG, JPEG, PNG</strong> (Maks. 4MB).
        </p>
    </div>
</div>

<form action="/pengaturan/logo/{{ $kec->id }}" method="post" enctype="multipart/form-data" id="FormLogo">
    @csrf
    @method('PUT')

    <input type="file" name="logo_kec" id="logo_kec" class="d-none">
</form>

<style>
    .logo-upload-container:hover .logo-preview-wrapper {
        border-color: #3b82f6 !important;
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.25), 0 8px 10px -6px rgba(59, 130, 246, 0.2) !important;
    }
    .logo-upload-container:hover .logo-hover-overlay {
        opacity: 1 !important;
    }
    .logo-upload-container:hover .logo-hover-overlay i {
        transform: scale(1) !important;
    }
</style>

<script>
    $(document).ready(function() {
        $(document).on('click', '#EditLogo', function(e) {
            e.preventDefault();
            $('#logo_kec').trigger('click');
        });
    });
</script>
