@php
    // Pesan untuk pengguna kedua saat proses bunga untuk bulan yang sama sedang berjalan.
@endphp
<div class="alert alert-warning" role="alert" style="background-color: #fff8e1; color: #8a6d3b; border: 1px solid #ffe69c;">
    <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-right: 10px; vertical-align: top;"></i>
    <div>
        <strong>Proses hitung bunga sedang berjalan.</strong><br>
        {{ $pesan }}
    </div>
</div>
