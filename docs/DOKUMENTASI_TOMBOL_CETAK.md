# 📄 Pemetaan Tombol Dokumen Untuk Cetak

**Status**: Dokumentasi Komprehensif  
**Total Tombol**: 15+ tombol distribusi di 8+ modul utama  
**Total Rute Dokumen**: 20+ rute cetak  
**Terakhir Diperbarui**: 2024

---

## 📊 Ringkasan Eksekutif

| Kategori | Jumlah Tombol | Jumlah Rute | Module |
|----------|--------------|----------|---------|
| **Transaksi Angsuran** | 3 | 4 | `transaksi/jurnal_angsuran` |
| **Transaksi Umum** | 2 | 2-3 | `transaksi/jurnal_umum` |
| **Pinjaman Individual** | 2 | 3+ | `perguliran_i` |
| **Pinjaman Kelompok** | 1+ | 2+ | `perguliran` |
| **Simpanan** | 1+ | 1+ | `simpanan` |
| **SOP & Administrasi** | 1+ | 1+ | `sop` |
| **Controllers (Backend)** | 2 | 10+ | `app/Http/Controllers` |
| **Aset & Konfigurasi** | 3 | - | `assets` |
| **TOTAL** | **15+** | **20+** | - |

---

## 1️⃣ TRANSAKSI - Jurnal Angsuran (Notifikasi Individu)

### 📍 Lokasi File
- **File**: `resources\views\transaksi\jurnal_angsuran\partials\notif_individu.blade.php`
- **Line**: 15-32 (3 tombol dalam 1 kontainer)
- **Trigger**: Notifikasi jatuh tempo angsuran individual

### 🔘 Tombol & Rute

| No. | Tombol | Kelas | Ikon | Target Route | Format | Fungsi |
|-----|--------|-------|------|-------------|--------|---------|
| 1.1 | Struk Dot Matrix | `btn-linkedin` | `fa-file` | `/transaksi/dokumen/struk_matrix_individu/{idtp}` | Dot Matrix | Cetak struk angsuran format dot matrix |
| 1.2 | BKM (Angsuran) | `btn-instagram` | `fa-file-circle-exclamation` | `/transaksi/dokumen/bkm_angsuran/{idt}` | PDF/Print | Cetak bukti kas masuk angsuran |
| 1.3 | Cetak Pada Kartu | `btn-tumblr` | `fa-file-invoice` | `/perguliran_i/dokumen/kartu_angsuran/{id_pinkel}/{idtp}` | PDF | Cetak pada kartu angsuran |

### 🎯 Data Context
- `$idtp`: ID Transaksi Rencana (RencanaAngsuran)
- `$idt`: ID Transaksi (Transaksi)
- `$id_pinkel`: ID Pinjaman Individual (PinjamanIndividu)

### 📋 Template Document Target
- Dokumen: `resources\views\transaksi\jurnal_angsuran\dokumen\struk.blade.php`

---

## 2️⃣ TRANSAKSI - Jurnal Umum (List Transaksi)

### 📍 Lokasi File
- **File**: `resources\views\transaksi\jurnal_umum\partials\jurnal.blade.php`
- **Line**: 235-260 (2 tombol kondisional)
- **Trigger**: List jurnal umum dengan detail transaksi

### 🔘 Tombol & Rute

| No. | Tombol | Kelas | Ikon | Kondisi | Target Route | Fungsi |
|-----|--------|-------|------|---------|-------------|---------|
| 2.1 | Dokumen (Angsuran) | `btn-tumblr` | `fa-file-circle-exclamation` | `$trx->idtp > 0 && $trx->id_pinj != 0` | `/transaksi/dokumen/{$files}_angsuran/{$trx->idt}` | Cetak dokumen transaksi angsuran |
| 2.2 | Dokumen (Reguler) | `btn-tumblr` | `fa-file-circle-exclamation` | ELSE | `/transaksi/dokumen/{$files}/{$trx->idt}` | Cetak dokumen transaksi umum |
| 2.3 | Reversal | `btn-tumblr` | `fa-code-pull-request` | `$is_dir == true` | N/A (In-page action) | Reversal transaksi |

### 🎯 Data Context
- `$files`: Tipe dokumen (BKM, BKK, BM, dll)
- `$trx->idt`: ID Transaksi
- `$trx->idtp`: ID Transaksi Rencana (jika ada)
- `$is_dir`: Flag untuk direktur (show reversal button)

### 📝 Catatan Penting
- Tombol reversal adalah action in-page, bukan cetak
- Tipe dokumen ditentukan oleh variabel `$files` yang di-set di controller
- Format cetak ditentukan oleh rute dinamis

---

## 3️⃣ PERGULIRAN_I - Pinjaman Individual (Detail Penerimaan)

### 📍 Lokasi File
- **File**: `resources\views\perguliran_i\partials\aktif.blade.php`
- **Line**: 187-220 (2 tombol dengan dropdown)
- **Trigger**: Tabel penerimaan/realisasi angsuran individual

### 🔘 Tombol & Rute

| No. | Tombol | Kelas | Ikon | Type | Target Route | Format | Fungsi |
|-----|--------|-------|------|------|-------------|--------|---------|
| 3.1.1 | Kuitansi | Dropdown Item | `fa-file` | Link | `/transaksi/dokumen/struk/{$real->id}` | Thermal | Cetak kuitansi (thermal standard) |
| 3.1.2 | Kuitansi Dot Matrix | Dropdown Item | `fa-file` | Link | `/transaksi/dokumen/struk_matrix/{$real->id}` | Dot Matrix | Cetak kuitansi format dot matrix |
| 3.1.3 | Kuitansi Thermal | Dropdown Item | `fa-file` | Link | `/transaksi/dokumen/struk_thermal/{$real->id}` | Thermal | Cetak kuitansi thermal standard |
| 3.2 | BKM | Button | `btn-github` | Action | `/perguliran_i/dokumen/kartu_angsuran/{$real->loan_id}/{$real->id}` | PDF | Cetak pada kartu angsuran |

### 🎯 Data Context
- `$real`: RealAngsuran record
- `$real->id`: ID Realisasi Angsuran
- `$real->loan_id`: ID Pinjaman Individual

### 🏗️ Struktur UI
```html
<div class="btn-group">
  <!-- Dropdown dengan 3 format kuitansi -->
  <button class="btn btn-instagram" data-bs-toggle="dropdown">
    <i class="fas fa-file"></i>
  </button>
  <ul class="dropdown-menu">
    <!-- 3 pilihan cetak -->
  </ul>
  
  <!-- Button terpisah untuk BKM -->
  <button class="btn btn-github" data-action="...">
    <i class="fas fa-file-invoice"></i>
  </button>
</div>
```

---

## 4️⃣ PERGULIRAN_I - Detail Pinjaman (Modal/Page)

### 📍 Lokasi File
- **File**: `resources\views\perguliran_i\detail.blade.php`
- **Line**: 1046-1050 (JavaScript event handler)
- **Trigger**: Tombol dengan atribut `data-action`

### 🔘 Event Handler

| Event | Selector | Aksi | Tujuan |
|-------|----------|------|--------|
| Click | `.btn-link` | `open_window(action)` | Buka jendela cetak baru dengan URL dari `data-action` |

### 📝 Catatan
- Handler ini adalah generic untuk semua tombol `.btn-link` dengan `data-action`
- Digunakan oleh multiple komponen (perguliran_i, transaksi, dll)
- Membuka dokumen di window baru (`target="_blank"`)

---

## 5️⃣ SIMPANAN - Cetak Formulir

### 📍 Lokasi File
- **File**: `resources\views\simpanan\partials\cetak_formulir.blade.php`
- **Line**: 520-531 (Auto-print on load)
- **Trigger**: View HTML+CSS untuk cetak simpanan

### 🔘 Fungsionalitas

| Komponen | Deskripsi | Fungsi |
|----------|-----------|---------|
| **Footer** | Menampilkan nama user & timestamp | Audit trail cetak |
| **Window.onload** | `window.print()` | Auto-trigger dialog cetak saat page load |
| **Format** | HTML + CSS print styles | Formatting untuk printer fisik |

### 📋 Informasi Printed
- Data simpanan member
- Nama user yang cetak
- Waktu cetak (Y-m-d H:i:s)

---

## 6️⃣ PERGULIRAN - Surat Kelayakan

### 📍 Lokasi File
- **File**: `resources\views\perguliran\dokumen\surat_kelayakan.blade.php`
- **Line**: 125-148
- **Trigger**: Dokumen surat kelayakan pinjaman kelompok

### 📝 Catatan
- Template dokumen (bukan tombol cetak)
- Di-render sebagai HTML atau PDF
- Biasanya dipanggil via controller dengan response PDF

---

## 7️⃣ SOP - Halaman Index

### 📍 Lokasi File
- **File**: `resources\views\sop\index.blade.php`
- **Line**: 113-136
- **Trigger**: Tab navigation untuk kustomisasi SOP

### 🔘 Tombol Navigation

| No. | Label | Ikon | Target Tab | Fungsi |
|-----|-------|------|-----------|--------|
| 7.1 | Kustomisasi CALK | `fa-solid fa-laptop-file` | `#tab-kustomisasi-calk` | Navigate ke tab edit CALK |
| 7.2 | Logo | `fa-solid fa-panorama` | `#tab-content-6` | Navigate ke tab manajemen logo |

### 📝 Catatan
- Ini adalah tab navigation, bukan cetak langsung
- Untuk akses kustomisasi dokumentasi & branding

---

## 8️⃣ TRANSAKSI - Jurnal Angsuran (Struk Document)

### 📍 Lokasi File
- **File**: `resources\views\transaksi\jurnal_angsuran\dokumen\struk.blade.php`
- **Line**: 122-152
- **Trigger**: Template dokumen struk (kuitansi) angsuran

### 📋 Konten
- Detail transaksi angsuran
- Informasi pinjaman
- Riwayat pembayaran
- Tanda tangan & logo

### 📝 Catatan
- Ini adalah template, bukan tombol
- Di-render ke PDF atau dicetak langsung
- Dipanggil dari controller TransaksiController

---

## 9️⃣ CONTROLLER - PinjamanIndividuController

### 📍 Lokasi File
- **File**: `app\Http\Controllers\PinjamanIndividuController.php`
- **Line**: 1331-1355
- **Method**: `dokumen(Request $request)`
- **Trigger**: Request ke `/perguliran_i/dokumen/*`

### 🔧 Fungsionalitas

| Langkah | Deskripsi |
|---------|-----------|
| 1 | Set default date variables (tahun, bulan, hari) |
| 2 | Get session location & fetch Kecamatan data |
| 3 | Get user data (director/kepala) |
| 4 | Set logo, nama lembaga, nama kecamatan |
| 5 | Format nama kabupaten untuk display |

### 📊 Data Returned
- `$tahun`, `$bulan`, `$hari`
- `$type` = 'pdf'
- `$logo`, `$nama_lembaga`, `$nama_kecamatan`
- `$nama_kabupaten`, `$kabupaten`, `$nama_kab`
- `$dir` (user direktur)
- `$kec` (kecamatan)

### 📝 Catatan
- Ini adalah base method untuk document routing
- Digunakan oleh multiple document types (SPK, Kartu Angsuran, dll)
- Extract user & lembaga info untuk document header

---

## 🔟 CONTROLLER - TransaksiController

### 📍 Lokasi File
- **File**: `app\Http\Controllers\TransaksiController.php`
- **Multiple Methods**: Lines 2158-2177, 2527-2555

### 📌 Methods & Routes

#### A) strukMatrix($id) [Line 2173-2180]
**Rute**: GET `/transaksi/dokumen/struk_matrix/{id}`
- **Purpose**: Render struk dalam format dot matrix
- **Data**: RealAngsuran dengan relasi trx & user
- **Return**: View HTML untuk struk matrix

#### B) struk($id) [Line 2158-2171]
**Rute**: GET `/transaksi/dokumen/struk/{id}`
- **Purpose**: Render struk standard (thermal)
- **Data**: RealAngsuran + RencanaAngsuran
- **Return**: View HTML untuk thermal printer

#### C) cetak($request) [Line 2527-2545]
**Rute**: POST `/transaksi/dokumen/cetak`
- **Purpose**: Generate PDF dokumen transaksi
- **Output**: PDF stream (A4 landscape)
- **Process**:
  1. Get Kecamatan & User data
  2. Set logo & keuangan utilities
  3. Render view 'transaksi.dokumen.cetak'
  4. Convert HTML ke PDF via DOMPDF
  5. Stream PDF ke browser

#### D) cetakKuitansi() [Line 2548-2550]
**Rute**: GET `/transaksi/dokumen/kuitansi/{id}`
- **Status**: Currently empty (stub)
- **Purpose**: Intended untuk cetak kuitansi standalone

#### E) lpp($id) [Line 2552-2560]
**Rute**: GET `/transaksi/dokumen/lpp/{id}`
- **Purpose**: Generate Laporan Laba Rugi (LPP)
- **Data**: PinjamanKelompok dengan relasi
- **Return**: LPP view

### 🎯 Rute Document yang Didukung

| Rute Pattern | Method | Format | Dokumen |
|-------------|--------|--------|----------|
| `/transaksi/dokumen/struk/{id}` | struk() | HTML/PDF | Kuitansi Thermal |
| `/transaksi/dokumen/struk_matrix/{id}` | strukMatrix() | HTML | Kuitansi Dot Matrix |
| `/transaksi/dokumen/struk_thermal/{id}` | struk() | HTML | Kuitansi Thermal |
| `/transaksi/dokumen/bkm_angsuran/{id}` | ? | PDF | BKM Angsuran |
| `/transaksi/dokumen/bkk/{id}` | ? | PDF | BKK |
| `/transaksi/dokumen/cetak` | cetak() | PDF | Dokumen Transaksi (bulk) |
| `/transaksi/dokumen/kuitansi/{id}` | cetakKuitansi() | PDF | Kuitansi |

---

## 1️⃣1️⃣ RUTE CETAK YANG TERSEDIA

### 📋 Berdasarkan Code Search & Controllers

#### TRANSAKSI GROUP
```
/transaksi/dokumen/struk/{id}                  → Kuitansi (Thermal)
/transaksi/dokumen/struk_matrix/{id}           → Kuitansi (Dot Matrix)
/transaksi/dokumen/struk_thermal/{id}          → Kuitansi (Thermal)
/transaksi/dokumen/bkm/{id}                    → Bukti Kas Masuk
/transaksi/dokumen/bkk/{id}                    → Bukti Kas Keluar
/transaksi/dokumen/bm/{id}                     → Bukti Mutasi
/transaksi/dokumen/bkm_angsuran/{id}           → BKM Angsuran
/transaksi/dokumen/cetak                       → Cetak Bulk Dokumen
/transaksi/dokumen/kuitansi/{id}               → Kuitansi (Invoice)
/transaksi/dokumen/lpp/{id}                    → Laporan Laba Rugi
```

#### PERGULIRAN_I GROUP (Pinjaman Individual)
```
/perguliran_i/dokumen/spk/{id}                 → Surat Perjanjian Kredit
/perguliran_i/dokumen/kartu_angsuran/{id1}/{id2}  → Kartu Angsuran
```

#### PERGULIRAN GROUP (Pinjaman Kelompok)
```
/perguliran/dokumen/surat_kelayakan/{id}       → Surat Kelayakan
/perguliran/dokumen/...                         → Dokumen lainnya
```

#### SIMPANAN GROUP
```
/simpanan/cetak_formulir/{id}                  → Formulir Simpanan
```

#### PELAPORAN GROUP
```
/pelaporan/arus_kas                            → Laporan Arus Kas
/pelaporan/neraca_saldo                        → Neraca Saldo
/pelaporan/calk                                → CALK
/pelaporan/rekap_calk                          → Rekap CALK
/pelaporan/jurnal_umum                         → Jurnal Umum
```

---

## 1️⃣2️⃣ BUTTON STYLING & CLASSES

### 🎨 Button Colors (Bootstrap Mapping)

| Kelas | Warna | Penggunaan |
|-------|-------|-----------|
| `btn-instagram` | 🔴 Pink/Red | Kuitansi, dokumen umum |
| `btn-tumblr` | 🔵 Blue/Cyan | Dokumen angsuran, alternatif |
| `btn-linkedin` | 🔷 LinkedIn Blue | Format alternatif (dot matrix) |
| `btn-github` | ⚫ Dark Gray | BKM, file invoice |
| `btn-white` | ⚪ White | Tab navigation, default |

### 🔲 Button Attributes

| Attribute | Deskripsi | Contoh |
|-----------|-----------|--------|
| `btn-icon-only` | Hanya tampil ikon, text hidden | `<button class="btn btn-instagram btn-icon-only">` |
| `btn-tooltip` | Enable Bootstrap tooltip | `data-bs-toggle="tooltip"` |
| `btn-link` | Styling link (transparent bg) | Generic button link |
| `data-action` | Target URL untuk cetak | `data-action="/transaksi/dokumen/struk/123"` |
| `target="_blank"` | Buka di window/tab baru | Direct link buttons |

### 🎯 Icon Pattern

```
<span class="btn-inner--icon">
  <i class="fas fa-{icon}"></i>
</span>
```

**Ikon Umum**:
- `fa-file` → Dokumen umum
- `fa-file-invoice` → Invoice/BKM
- `fa-file-circle-exclamation` → Dokumen dengan warning
- `fa-code-pull-request` → Reversal

---

## 1️⃣3️⃣ JAVASCRIPT EVENT HANDLERS

### 🔌 Handler 1: data-action Click

**File**: `resources\views\perguliran_i\detail.blade.php` Line 1046-1050

```javascript
$(document).on('click', '.btn-link', function(e) {
    var action = $(this).attr('data-action')
    open_window(action)
})
```

**Fungsi**:
- Catch semua click pada tombol dengan class `.btn-link`
- Extract `data-action` attribute
- Panggil `open_window()` function dengan action URL
- Result: Buka dokumen di window/tab baru

**Digunakan Oleh**:
- Tombol BKM di perguliran_i detail
- Tombol dokumen di transaksi jurnal umum
- Tombol struk dot matrix di notif individu

### 🔌 Handler 2: Dropdown Toggle

**File**: Multiple files

```html
<button class="btn btn-instagram btn-icon-only" 
        data-bs-toggle="dropdown">
    <i class="fas fa-file"></i>
</button>
<ul class="dropdown-menu">
    <li><a href="/route1">Option 1</a></li>
    <li><a href="/route2">Option 2</a></li>
</ul>
```

**Fungsi**:
- Bootstrap dropdown menu
- Click main button untuk toggle dropdown
- Select option untuk navigate ke rute

**Digunakan Oleh**:
- Tombol kuitansi di perguliran_i aktif (3 format)

---

## 1️⃣4️⃣ SECURITY & ACCESS CONTROL

### 🔐 Kontrol Akses yang Ditemukan

| Level | File | Method | Kontrol |
|-------|------|--------|---------|
| 1 | PinjamanIndividuController | dokumen() | Session `lokasi` |
| 2 | TransaksiController | struk(), strukMatrix() | Session `lokasi` |
| 3 | Blade Templates | Multiple | Session/Auth checks |

### 📋 Session Context
- Semua routes menggunakan `Session::get('lokasi')` untuk filter data
- Multi-tenant architecture: data diisolasi per lokasi/branch
- Kontrol akses dilakukan di middleware/controller, bukan di view

### ⚠️ Catatan Keamanan
- Data table names dinamis: `saldo_{lokasi}`
- Important: Verify session context tidak dapat di-bypass
- Semua document routes harus validate user authorization

---

## 1️⃣5️⃣ RESPONSE TYPES & FORMATS

### 📊 Format Output

| Format | Method | Library | Keterangan |
|--------|--------|---------|-----------|
| **HTML** | view() | Laravel Blade | Langsung di-render browser |
| **PDF** | PDF::loadHTML() | DOMPDF | Convert HTML ke PDF stream |
| **Print** | window.print() | JavaScript | Trigger browser print dialog |
| **Dot Matrix** | HTML+CSS | Custom CSS | Format khusus printer dot matrix |
| **Thermal** | HTML+CSS | Custom CSS | Format khusus thermal printer 58mm |

### 🔄 Response Handling

```php
// Response 1: View HTML (langsung cetak di browser)
return view('transaksi.dokumen.struk', $data);

// Response 2: PDF Stream
$pdf = PDF::loadHTML($view)->setPaper('A4', 'landscape');
return $pdf->stream();

// Response 3: Window Print (JavaScript)
window.onload = function() { window.print(); }
```

---

## 1️⃣6️⃣ DOKUMENTASI TEMPLATE

### 📄 Template Files untuk Cetak

| File | Rute Trigger | Fungsi | Komponen |
|------|-------------|--------|----------|
| `transaksi/dokumen/struk.blade.php` | `/struk/{id}` | Kuitansi thermal | Logo, detail transaksi, signature |
| `transaksi/dokumen/cetak.blade.php` | `/cetak` | Dokumen bulk | List transaksi, total |
| `transaksi/jurnal_angsuran/dokumen/struk.blade.php` | `/struk*` | Angsuran slip | Detail angsuran, riwayat |
| `perguliran_i/dokumen/spk.blade.php` | `/spk/{id}` | Perjanjian kredit | 8 Pasal, signature blocks |
| `simpanan/partials/cetak_formulir.blade.php` | `/cetak_formulir` | Formulir simpanan | Data simpanan, audit footer |
| `perguliran/dokumen/surat_kelayakan.blade.php` | `/surat_kelayakan` | Rekomendasi kredit | Penilaian, recommendation |

### 🔧 Template Data Context

Semua template menerima data:
```php
$tahun          // Tahun dokumen
$bulan          // Bulan dokumen
$hari           // Hari dokumen
$type           // Format (pdf/html)
$logo           // Path logo lembaga
$nama_lembaga   // Nama institusi
$kec            // Kecamatan model
$kab            // Kabupaten model
$dir            // User direktur
$keuangan       // Keuangan utility
```

---

## 1️⃣7️⃣ DEVELOPMENT NOTES

### 🚧 Incomplete/Stub Methods
- `TransaksiController::cetakKuitansi()` (Line 2548-2550) - Empty stub, needs implementation

### 🔍 Code Patterns

**Pattern 1: Dropdown Document Formats**
```html
<button class="btn btn-instagram" data-bs-toggle="dropdown">
  <i class="fas fa-file"></i>
</button>
<ul class="dropdown-menu">
  <li><a href="/dokumen/format1/{id}">Format 1</a></li>
  <li><a href="/dokumen/format2/{id}">Format 2</a></li>
</ul>
```

**Pattern 2: data-action with JavaScript Handler**
```html
<button class="btn-link" data-action="/route/{id}">
  <i class="fas fa-file"></i>
</button>
<!-- Handler di detail.blade.php line 1046 -->
```

**Pattern 3: Form with Checkboxes for Bulk Print**
```html
<form action="/cetak" method="post" id="FormCetakDokumen">
  @foreach ($items as $item)
    <input type="checkbox" name="cetak[]" value="{{ $item->id }}">
  @endforeach
</form>
```

### 🎯 Improvement Opportunities
1. Centralize document routes ke single DocumentController
2. Implement unified document template engine
3. Add permission/role checks di middleware
4. Create document queue untuk bulk printing
5. Implement document history/audit log

---

## 1️⃣8️⃣ QUICK REFERENCE CHEATSHEET

### 🔗 Untuk Menambah Tombol Cetak Baru

**Step 1: Buat tombol di Blade template**
```html
<button type="button" class="btn btn-instagram btn-tooltip"
        data-action="/rute/dokumen/{{ $id }}"
        data-bs-toggle="tooltip" title="Cetak">
    <i class="fas fa-file"></i>
</button>
```

**Step 2: Handler sudah exist di JavaScript**
- File: `resources\views\perguliran_i\detail.blade.php`
- Event: `.btn-link` click handler → `open_window(action)`

**Step 3: Implement controller method**
```php
public function dokumen($id) {
    $data = Model::find($id);
    return view('template.cetak', $data);
    // atau
    // return PDF::loadHTML(view(...)->render())->stream();
}
```

**Step 4: Register route di `routes/web.php`**
```php
Route::get('/rute/dokumen/{id}', 'Controller@dokumen');
```

---

## 1️⃣9️⃣ FILE SUMMARY TABLE

| File | Type | Tombol | Rute | Fungsi |
|------|------|--------|------|--------|
| `notif_individu.blade.php` | View | 3 | 3 | Notif angsuran dengan cetak |
| `jurnal.blade.php` | View | 2 | 2-3 | Jurnal dengan dokumen |
| `aktif.blade.php` | View | 2 | 4 | Dropdown kuitansi + BKM |
| `detail.blade.php` | View | 0 | 0 | Handler JS untuk cetak |
| `cetak_formulir.blade.php` | View | 0 | 0 | Auto-print simpanan |
| `surat_kelayakan.blade.php` | Template | 0 | 0 | Template saja |
| `struk.blade.php` | Template | 0 | 0 | Template angsuran |
| `spk.blade.php` | Template | 0 | 0 | Template SPK (sedang dilihat) |
| `PinjamanIndividuController.php` | Controller | 0 | 1+ | Base dokumen method |
| `TransaksiController.php` | Controller | 0 | 8+ | Multiple dokumen routes |

---

## 2️⃣0️⃣ LINKS & REFERENSI

### 📂 Related Files
- Main layout: `resources\views\perguliran_i\dokumen\layout\base.blade.php`
- Utilities: `app\Utils\Keuangan.php`, `app\Utils\Tanggal.php`
- Models: `app\Models\*.php`
- Routes: `routes\web.php`

### 📚 Dokumentasi External
- Bootstrap Buttons: https://getbootstrap.com/docs/5.0/components/buttons/
- Font Awesome Icons: https://fontawesome.com/icons
- DOMPDF: https://github.com/barryvdh/laravel-dompdf
- Laravel Blade: https://laravel.com/docs/blade

---

## 📌 KESIMPULAN

- **Total Tombol Cetak**: 15+ button distribusi di berbagai modul
- **Total Rute Dokumen**: 20+ route untuk berbagai dokumen
- **Button Styles**: 5 warna utama (Instagram pink, Tumblr blue, LinkedIn, GitHub, White)
- **Response Types**: HTML, PDF, Print dialog, Format khusus (Dot Matrix, Thermal)
- **Access Control**: Multi-tenant per session lokasi
- **Main Controllers**: PinjamanIndividuController, TransaksiController
- **JavaScript Handler**: Generic `.btn-link` click handler di detail.blade.php

**Last Updated**: 2024  
**Maintained By**: Development Team  
**Status**: Production Ready
