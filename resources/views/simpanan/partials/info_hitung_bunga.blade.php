@php
    use Carbon\Carbon;
@endphp

<span>
    Proses Hitung Bunga ini akan menghitung

        <strong>
        @if ($jb == 1)
            Saldo Terakhir
        @elseif ($jb == 2)
            Saldo Terendah
        @else
            Saldo Rata-Rata
        @endif
        </strong>

    simpanan / tabungan dari tanggal
    <strong>{{ \Carbon\Carbon::parse($tgl_awal)->translatedFormat('j F Y') }}</strong> hingga tanggal 
    <strong>{{ \Carbon\Carbon::parse($tgl_akhir)->translatedFormat('j F Y') }} ({{ $jumlah_hari }} hari)</strong>, 
    jika tanggal kurang sesuai, silakan atur di Pengaturan SOP atau bisa menghubungi TS (<strong>+62 882-0066-44656</strong>)
    <br>
    <br>
    Biarkan CIF kosong untuk memproses semua CIF. Jika hanya ingin memproses beberapa CIF tertentu, silakan masukkan dalam format: <strong>CIF1, CIF2, CIF3</strong>, dan seterusnya. (misal : 1, 2, 4, 34, dsb)
</span>

@if (!empty($jumlahSudahDiproses) && $jumlahSudahDiproses > 0)
    <div class="alert alert-warning mt-3" role="alert" style="background-color: #fff8e1; color: #8a6d3b; border: 1px solid #ffe69c; display: block;">
        <i class="fas fa-exclamation-triangle" style="font-size: 1.3rem; margin-right: 8px; vertical-align: top;"></i>
        <div>
            <strong>Perhatian: bulan ini pernah diproses.</strong><br>
            Tanggal proses <strong>{{ \Carbon\Carbon::parse($tgl_trans)->translatedFormat('j F Y') }}</strong>
            sudah memiliki transaksi bunga pada <strong>{{ number_format($jumlahSudahDiproses, 0, ',', '.') }}</strong>
            rekening (dari total {{ number_format($jumlahRekeningAktif, 0, ',', '.') }} rekening aktif).<br>
            Rekening yang sudah punya transaksi bulan ini <strong>akan dilewati</strong> saat proses dijalankan,
            sehingga tidak terjadi transaksi dobel.
        </div>
    </div>
@endif
