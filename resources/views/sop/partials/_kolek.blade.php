@php
    use App\Utils\Keuangan;
    $pembulatan = (string) $kec->pembulatan;

    $sistem = 'auto';
    if (Keuangan::startWith($pembulatan, '+')) {
        $sistem = 'keatas';
        $pembulatan = intval($pembulatan);
    }

    if (Keuangan::startWith($pembulatan, '-')) {
        $sistem = 'kebawah';
        $pembulatan = intval($pembulatan * -1);
    }
    $kolekDb = $kec->kolek ? json_decode($kec->kolek, true) : [];

    $kolekDefaults = [
        ['nama' => '', 'prosentase' => '', 'durasi' => '', 'satuan' => 'hari'],
        ['nama' => '', 'prosentase' => '', 'durasi' => '', 'satuan' => 'hari'],
        ['nama' => '', 'prosentase' => '', 'durasi' => '', 'satuan' => 'hari'],
        ['nama' => '', 'prosentase' => '', 'durasi' => '', 'satuan' => 'hari'],
        ['nama' => '', 'prosentase' => '', 'durasi' => '', 'satuan' => 'hari'],
    ];

    // merge: kalau ada di DB, timpa default
    $kolekValues = array_replace_recursive($kolekDefaults, $kolekDb);

    // satuan global (diambil dari tingkat 1, fallback ke 'hari')
    $globalSatuan = old('satuan_global', $kolekValues[0]['satuan'] ?? 'hari');
@endphp

<form action="/pengaturan/kolek/{{ $kec->id }}" method="post" id="Formkolek">
    @csrf
    @method('PUT')

    {{-- Satuan Durasi global (mengontrol semua tingkat) --}}
    <div class="row">
        <div class="col-md-12 d-flex justify-content-end mb-2">
            <div style="min-width: 180px;">
                <label for="satuan_global" class="form-label small mb-1">Satuan Durasi</label>
                <div class="select-wrapper">
                    <select name="satuan_global" id="satuan_global" class="form-control form-control-sm">
                        <option value="hari" {{ $globalSatuan == 'hari' ? 'selected' : '' }}>Hari</option>
                        <option value="bulan" {{ $globalSatuan == 'bulan' ? 'selected' : '' }}>Bulan</option>
                    </select>
                    <span class="select-arrow" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>

@for ($i = 0; $i < 5; $i++)
    @php
        $tingkat = $i + 1;
        // Mulai Dari = Sampai Dengan tingkat sebelumnya (atau 0 untuk tingkat 1)
        $prevDurasi = $i === 0 ? 0 : (int) (old('durasi'.($i), $kolekValues[$i-1]['durasi']));
    @endphp
<div class="row">
    <!-- Nama Kolek -->
    <div class="col-md-3">
        <div class="position-relative mb-3">
            <label for="nama_kolek{{ $tingkat }}" class="form-label">Kolek Tingkat {{ $tingkat }}</label>
            <input type="text" name="nama_kolek{{ $tingkat }}" id="nama_kolek{{ $tingkat }}"
                class="form-control"
                value="{{ old('nama_kolek'.$tingkat, $kolekValues[$i]['nama']) }}">
            <small class="text-danger" id="msg_nama_kolek{{ $tingkat }}"></small>
        </div>
    </div>

    <!-- Prosentase -->
    <div class="col-md-3">
        <div class="position-relative mb-3">
            <label for="pros_kolek{{ $tingkat }}" class="form-label">Prosentase Kolek {{ $tingkat }}</label>
            <input type="number" name="pros_kolek{{ $tingkat }}" id="pros_kolek{{ $tingkat }}"
                class="form-control"
                value="{{ old('pros_kolek'.$tingkat, $kolekValues[$i]['prosentase']) }}">
            <small class="text-danger" id="msg_pros_kolek{{ $tingkat }}"></small>
        </div>
    </div>

    <!-- Mulai Dari (read-only, otomatis) -->
    <div class="col-md-3">
        <div class="position-relative mb-3">
            <label for="mulai_dari{{ $tingkat }}" class="form-label">Mulai Dari</label>
            <input type="number" id="mulai_dari{{ $tingkat }}"
                class="form-control mulai-dari"
                value="{{ $prevDurasi }}"
                readonly
                disabled>
        </div>
    </div>

    <!-- Sampai Dengan (yang disimpan sebagai durasi) -->
    <div class="col-md-3">
        <div class="position-relative mb-3">
            <label for="durasi{{ $tingkat }}" class="form-label">Sampai Dengan</label>
            <div class="input-group">
                <input type="number" name="durasi{{ $tingkat }}" id="durasi{{ $tingkat }}"
                    class="form-control durasi-sampai"
                    value="{{ old('durasi'.$tingkat, $kolekValues[$i]['durasi']) }}">
                <span class="input-group-text satuan-label">{{ ucfirst($globalSatuan) }}</span>
            </div>
            <small class="text-danger" id="msg_durasi{{ $tingkat }}"></small>
        </div>
    </div>

    {{-- hidden input supaya proses simpan (satuan per tingkat) tetap jalan --}}
    <input type="hidden" name="satuan{{ $tingkat }}" id="satuan{{ $tingkat }}" value="{{ $globalSatuan }}">
</div>
@endfor

<!-- Note -->
<div class="alert alert-warning mt-3" role="alert">
    Catatan: Apabila sistem hanya menggunakan tiga tingkat kolektibilitas, maka isian untuk Kolek Tingkat 4 dan Kolek Tingkat 5 dapat dikosongkan.
</div>

</form>

<div class="d-flex justify-content-end">
    <button type="button" id="Simpankolek" data-target="#Formkolek"
        class="btn btn-sm btn-dark mb-0 btn-simpan">
        Simpan Perubahan
    </button>
</div>

<style>
    .select-wrapper {
        position: relative;
    }
    .select-wrapper select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 28px;
        background-image: none;
    }
    .select-wrapper .select-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        width: 0;
        height: 0;
        pointer-events: none;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #495057;
        transform: translateY(-50%);
    }
</style>

<script>
(function () {
    const satuanGlobal = document.getElementById('satuan_global');
    const satuanLabels = ['satuan1','satuan2','satuan3','satuan4','satuan5'];
    const durasiInputs = document.querySelectorAll('.durasi-sampai');

    function applySatuan(value) {
        // update label "Hari"/"Bulan" di samping input
        document.querySelectorAll('.satuan-label').forEach(function (el) {
            el.textContent = value.charAt(0).toUpperCase() + value.slice(1);
        });
        // update hidden input satuan per tingkat
        satuanLabels.forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = value;
        });
    }

    function refreshMulaiDari() {
        let prev = 0;
        durasiInputs.forEach(function (input, idx) {
            const mulai = document.getElementById('mulai_dari' + (idx + 1));
            if (mulai) mulai.value = prev;
            const v = parseInt(input.value, 10);
            prev = isNaN(v) ? 0 : v;
        });
    }

    if (satuanGlobal) {
        satuanGlobal.addEventListener('change', function () {
            applySatuan(this.value);
        });
        // inisialisasi awal
        applySatuan(satuanGlobal.value);
    }

    durasiInputs.forEach(function (input) {
        input.addEventListener('input', refreshMulaiDari);
        input.addEventListener('change', refreshMulaiDari);
    });

    refreshMulaiDari();
})();
</script>
