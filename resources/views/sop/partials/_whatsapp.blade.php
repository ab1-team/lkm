@php
    $pesan_wa = json_decode($kec->whatsapp, true);
    if (!$pesan_wa) {
        $pesan_wa = [
            'tagihan' => '',
            'angsuran' => '',
        ];
    }
@endphp

<form action="/pengaturan/pesan_whatsapp/{{ $kec->id }}" method="post" id="FormScanWhatsapp">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6">
            <div class="position-relative mb-3">
                <label class="form-label" for="tagihan">Pesan Tagihan</label>
                <textarea class="form-control" name="tagihan" id="tagihan" cols="20" rows="10">{!! $pesan_wa['tagihan'] !!}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="position-relative mb-3">
                <label class="form-label" for="angsuran">Pesan Angsuran</label>
                <textarea class="form-control" name="angsuran" id="angsuran" cols="20" rows="10">{!! $pesan_wa['angsuran'] !!}</textarea>
            </div>
        </div>
    </div>

    <div class="card shadow-none border mb-3">
        <div class="card-body p-3">
            <h6 class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 mb-3">Variabel yang Tersedia (Klik untuk Salin)</h6>
            <div class="d-flex flex-wrap gap-2" style="gap: 8px;">
                @php
                    $placeholders = [
                        '{Nama Nasabah}' => 'bg-gradient-primary',
                        '{Nama Desa}' => 'bg-gradient-info',
                        '{Angsuran Pokok}' => 'bg-gradient-success',
                        '{Angsuran Jasa}' => 'bg-gradient-success',
                        '{Tanggal Jatuh Tempo}' => 'bg-gradient-warning',
                        '{Tanggal Bayar}' => 'bg-gradient-warning',
                        '{User Login}' => 'bg-gradient-secondary',
                        '{Telpon}' => 'bg-gradient-secondary'
                    ];
                @endphp
                @foreach($placeholders as $tag => $color)
                    <span class="badge {{ $color }} cursor-pointer" 
                          onclick="copySakti('{{ $tag }}', this)"
                          style="text-transform: none; font-size: 12px; transition: all 0.2s;">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </div>
</form>

<script>
    function copySakti(text, element) {
        // Buat textarea asli, jangan disembunyikan terlalu jauh agar browser tidak curiga
        var textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Letakkan di luar layar tapi tetap 'visible'
        textArea.style.position = "absolute";
        textArea.style.left = "-9999px";
        textArea.style.top = document.documentElement.scrollTop + "px";
        document.body.appendChild(textArea);
        
        textArea.focus();
        textArea.select();
        textArea.setSelectionRange(0, 99999); // Untuk mobile
        
        var berhasil = false;
        try {
            berhasil = document.execCommand('copy');
        } catch (err) {
            berhasil = false;
        }
        
        if (berhasil) {
            var $el = $(element);
            var originalText = $el.text();
            $el.text('Tersalin!');
            $el.addClass('bg-gradient-dark');
            
            setTimeout(function() {
                $el.text(originalText);
                $el.removeClass('bg-gradient-dark');
            }, 1000);
        } else {
            // Jika cara sakti gagal, tampilkan prompt manual sebagai pertolongan pertama
            prompt("Gagal copy otomatis. Silakan copy manual dari sini:", text);
        }
        
        document.body.removeChild(textArea);
    }
</script>

<div class="d-flex justify-content-end">
    <button type="button" id="HapusWa" class="btn btn-sm btn-danger mb-0 me-2" style="display: none;">
        Hapus Whatsapp
    </button>
    <button type="button" id="ScanWA" class="btn btn-sm btn-info mb-0 me-2" style="display: none;">
        Scan Whatsapp
    </button>
    <button type="button" id="CreateInstance" class="btn btn-sm btn-primary mb-0 me-2" style="display: none;">
        Buat Instance
    </button>
    <button type="button" id="SimpanWhatsapp" data-target="#FormScanWhatsapp"
        class="btn btn-sm btn-dark mb-0 btn-simpan">
        Simpan Perubahan
    </button>
</div>

<div class="modal fade" id="ModalScanWA" tabindex="-1" aria-labelledby="ModalScanWALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalScanWALabel">
                    Scan Whatsapp Gateway
                    <a href="#" id="RefreshQR" class="ms-2 text-info" title="Refresh QR Code">
                        <i class="fa-solid fa-sync text-sm"></i>
                    </a>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="/assets/img/qr.png" id="QrCode" alt="QR Code" class="img-fluid"
                    style="max-width: 300px; border: 1px solid #eee; padding: 10px;">
                <div class="mt-3">
                    <ul id="Pesan" style="list-style: none; padding: 0;" class="text-sm">
                        <li>Pastikan WhatsApp Gateway menyala.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
