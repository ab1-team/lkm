# Dokumentasi Implementasi Fitur Notifikasi "Update Fitur"

Dokumen ini menjelaskan langkah-langkah teknis yang dilakukan saat mengimplementasikan fitur notifikasi update fitur pada project Laravel ini.

---

## 1. Tujuan Fitur

Fitur notifikasi "Update Fitur" memungkinkan admin untuk mempublikasikan informasi pembaruan sistem (fitur baru, perbaikan bug, pengumuman, maintenance) yang akan ditampilkan sebagai:

- **Icon lonceng** di header/navbar dengan badge jumlah notifikasi belum dibaca
- **Dropdown notifikasi** yang muncul saat lonceng diklik
- **Halaman timeline** yang menampilkan seluruh riwayat pembaruan

---

## 2. Tabel Database

Tabel `update_fitur` sudah tersedia di database (tidak perlu migration baru):

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | PK, auto increment | Primary key |
| `tanggal` | datetime | Tanggal publikasi |
| `judul` | varchar(30) | Judul pembaruan (maks 30 karakter) |
| `deskripsi` | text | Isi deskripsi pembaruan |
| `foto` | text | Nama file foto (disimpan di storage) |
| `jenis` | varchar/string | Kategori: fitur, perbaikan, pengumuman, maintenance |

---

## 3. File yang Dibuat

### 3.1. Model

**`app/Models/UpdateFitur.php`**

Model Eloquent untuk tabel `update_fitur` dengan fitur:
- `$timestamps = false` (tabel tidak punya created_at/updated_at)
- `$fillable` mencakup semua kolom yang bisa diisi mass-assignment
- `$casts` mengubah kolom `tanggal` menjadi instance Carbon
- **Scope `dalamMasaNotif()`**: filter hanya update dalam 7 hari terakhir (untuk dropdown)
- **Scope `terbaruDulu()`**:排序 berdasarkan tanggal terbaru

### 3.2. Konfigurasi

**`config/update_fitur.php`**

File konfigurasi berisi:
- `masa_berlaku_hari`: 7 (notifikasi otomatis hilang dari lonceng)
- `jenis`: daftar kategori dengan label dan warna badge
  - `fitur` → "Fitur Baru" (primary/biru)
  - `perbaikan` → "Perbaikan Bug" (warning/kuning)
  - `pengumuman` → "Pengumuman" (info/cyan)
  - `maintenance` → "Maintenance" (secondary/abu)
- `cookie_name`: nama cookie penanda "sudah dibaca"
- `cookie_days`: 365 (umur cookie)

### 3.3. Middleware

**`app/Http/Middleware/LocalOnly.php`**

Middleware untuk membatasi akses hanya di environment lokal. **Saat ini tidak digunakan di route** setelah perubahan requirement, tapi file tetap ada untuk kemungkinan penggunaan di masa depan.

Registrasi di `app/Http/Kernel.php`:
```php
'local.only' => \App\Http\Middleware\LocalOnly::class,
```

### 3.4. Controller

**`app/Http/Controllers/UpdateFiturController.php`**

Controller untuk fitur publik/user (bell + timeline):

- `dropdown(Request $request)`: Method AJAX yang mengembalikan data notifikasi dalam format JSON. Menghitung unread count berdasarkan cookie read_at.
- `tandaiDibaca(Request $request)`: Method POST yang menyimpan cookie `notif_update_fitur_read_at` dengan timestamp sekarang (umur 365 hari).
- `timeline()`: Method GET yang menampilkan halaman timeline semua update fitur dengan pagination.

**`app/Http/Controllers/Admin/UpdateFiturController.php`**

Controller CRUD untuk admin:

- `index()`: Menampilkan daftar semua update fitur (pagination 10)
- `create()`: Menampilkan form tambah
- `store(Request $request)`: Menyimpan data baru + upload foto
- `edit(UpdateFitur $update_fitur)`: Menampilkan form edit
- `update(Request $request, UpdateFitur $update_fitur)`: Update data + ganti foto (jika ada)
- `destroy(UpdateFitur $update_fitur)`: **Hapus data + hapus file foto dari storage**
- `validated(Request $request)`: Validasi input (judul max 30, jenis sesuai config, foto image max 2MB)
- `simpanFoto(Request $request)`: Upload foto ke `storage/app/public/update-fitur/`, return nama file
- `hapusFotoLama(?string $filename)`: Hapus foto dari storage

### 3.5. Routes

**`routes/web.php`**

Penambahan:

```php
use App\Http\Controllers\UpdateFiturController;
use App\Http\Controllers\Admin\UpdateFiturController as AdminUpdateFiturController;

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi/dropdown', [UpdateFiturController::class, 'dropdown'])->name('notif.dropdown');
    Route::post('/notifikasi/tandai-dibaca', [UpdateFiturController::class, 'tandaiDibaca'])->name('notif.tandaiDibaca');
    Route::get('/notifikasi/timeline', [UpdateFiturController::class, 'timeline'])->name('notif.timeline');
});

Route::middleware('auth')->prefix('admin/update-fitur')->name('admin.updateFitur.')->group(function () {
    Route::resource('/', AdminUpdateFiturController::class)->parameters(['' => 'update_fitur']);
});
```

### 3.6. Views

**`resources/views/components/notif-bell.blade.php`**

Partial component untuk icon lonceng notifikasi di navbar. Digunakan sebagai `<li>` langsung di navbar dengan:
- Icon FontAwesome `fa-bell`
- Badge unread count (hidden jika 0)
- Dropdown menu yang di-load via AJAX

**`resources/views/update-fitur/timeline.blade.php`**

Halaman timeline yang menampilkan semua update fitur (extends `layouts.base`):
- Card per item dengan badge jenis
- Foto (jika ada)
- Pagination

**`resources/views/admin/update-fitur/index.blade.php`**

Halaman daftar update fitur untuk admin (extends `admin.layout.base`):
- Tabel dengan kolom: No, Tanggal, Judul, Jenis, Foto, Aksi
- Tombol Tambah, Edit, Hapus
- Pagination

**`resources/views/admin/update-fitur/form.blade.php`**

Form create/edit update fitur (extends `admin.layout.base`):
- Field: Tanggal (datetime-local), Judul (max 30), Jenis (select), Deskripsi (textarea), Foto (file input)
- Preview foto lama saat edit
- Validasi error display

### 3.7. JavaScript

**`public/js/notif-bell.js`**

Script untuk handle notifikasi bell:
- Load data notifikasi via AJAX ke `/notifikasi/dropdown`
- Render 5 item terbaru + tombol "Tandai sudah dibaca"
- POST ke `/notifikasi/tandai-dibaca` dengan CSRF token

---

## 4. File yang Diubah

### 4.1. `app/Http/Kernel.php`

Penambahan alias middleware:
```php
'local.only' => \App\Http\Middleware\LocalOnly::class,
```

### 4.2. `routes/web.php`

Penambahan import dan route group (lihat section 3.5).

### 4.3. `resources/views/layouts/base.blade.php`

Tiga perubahan:

1. **CSRF Token Meta** (di `<head>`):
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

2. **Include Bell di Navbar** (di antara user dropdown dan icon gear):
```blade
@auth
    @include('components.notif-bell')
@endauth
```

3. **Load JavaScript** (sebelum `</body>` atau setelah `@yield('script')`):
```html
<script src="{{ asset('js/notif-bell.js') }}"></script>
```

### 4.4. `.gitignore`

~~Penambahan~~ → **Dihapus**: Baris `/resources/views/admin/update-fitur/` dihapus dari `.gitignore` sehingga view admin ikut ter-track di git.

---

## 5. Mekanisme Kerja

### 5.1. Notifikasi Otomatis Hilang Setelah 7 Hari

- Query notifikasi menggunakan scope `dalamMasaNotif()` yang filter `tanggal >= now()->subDays(7)`
- Item yang lebih lama dari 7 hari tidak muncul di dropdown lonceng
- Item tetap ada di database dan muncul di halaman timeline (timeline tidak pakai filter 7 hari)

### 5.2. Status "Sudah Dibaca" via Cookie

- Cookie `notif_update_fitur_read_at` menyimpan timestamp terakhir user klik "Tandai sudah dibaca"
- **Unread count** = jumlah item dengan `tanggal >= now()-7hari` **DAN** `tanggal > cookie(read_at)`
- Jika cookie belum ada → semua notifikasi dalam 7 hari terakhir dianggap belum dibaca
- Cookie berumur 365 hari (tidak sering reset)

### 5.3. Upload & Hapus Foto

- Foto diupload ke `storage/app/public/update-fitur/` (disk `public`)
- Hanya **nama file** yang disimpan di kolom `foto` di database
- Saat edit dengan foto baru → foto lama dihapus dulu, lalu upload baru
- Saat hapus record → foto juga dihapus dari storage (mencegah penumpukan file orphan)

---

## 6. Cara Pakai

### 6.1. Admin Menambah Update Fitur

1. Login ke aplikasi
2. Akses `/admin/update-fitur`
3. Klik tombol "Tambah"
4. Isi form: Tanggal, Judul (max 30 karakter), Jenis, Deskripsi, Foto
5. Klik "Simpan"

### 6.2. User Melihat Notifikasi

1. Login ke aplikasi
2. Lonceng di navbar menampilkan badge jumlah notifikasi belum dibaca
3. Klik lonceng → dropdown menampilkan 5 notifikasi terbaru
4. Klik "Tandai sudah dibaca" → badge hilang (cookie di-set)
5. Klik "Lihat semua" → ke halaman timeline

### 6.3. Setup di Server Baru

Setelah deploy ke server baru, pastikan:

1. **Jalankan storage link** (WAJIB):
   ```bash
   php artisan storage:link
   ```
   Ini membuat symlink `public/storage` → `storage/app/public` supaya foto bisa diakses via URL.

2. **Set permission**:
   ```bash
   chmod -R 775 storage/
   chown -R www-data:www-data storage/
   ```

3. **Pastikan folder upload termigrasi**:
   Folder `storage/app/public/update-fitur/` harus ada di server. Jika deployment tidak include folder ini, foto tidak akan muncul meskipun tabel `update_fitur` sudah terisi.

4. **Cek APP_URL** di `.env` server harus sesuai dengan domain.

---

## 7. Troubleshooting

### 7.1. Foto Tidak Muncul di Timeline / Server Online

**Gejala**: Foto terlihat di local, tapi tidak muncul di server online.

**Penyebab paling umum**: `php artisan storage:link` belum dijalankan di server, atau folder `storage/app/public/update-fitur/` belum ter-upload.

**Solusi**:
1. SSH ke server dan jalankan `php artisan storage:link`
2. Pastikan folder `storage/app/public/update-fitur/` ada dan berisi file foto
3. Cek permission folder (harus readable oleh web server)
4. Cek `.env` → `APP_URL` harus sesuai domain

### 7.2. Error "Undefined variable $title"

**Penyebab**: View extends layout yang membutuhkan variabel `$title`, tapi controller tidak mengirimkannya.

**Solusi**: Pastikan semua method controller yang return view mengirim variabel `$title`:
```php
$title = 'Judul Halaman';
return view('nama.view', compact('items', 'title'));
```

### 7.3. Bell Tidak Muncul di Navbar

**Penyebab**: Cache view lama atau user belum login.

**Solusi**:
1. Jalankan `php artisan view:clear`
2. Pastikan user sudah login (`@auth` directive)
3. Cek console browser untuk error JavaScript

### 7.4. Badge Tidak Update

**Penyebab**: JavaScript belum load atau cookie belum ter-set.

**Solusi**:
1. Cek apakah file `public/js/notif-bell.js` ada dan ter-load
2. Cek console browser untuk error
3. Pastikan route `/notifikasi/dropdown` bisa diakses (cek `php artisan route:list`)

---

## 8. Catatan Implementasi

- **Perubahan requirement**: Middleware `local.only` awalnya digunakan untuk admin route, tapi kemudian dihapus karena admin route hanya butuh `auth` saja.
- **Perubahan requirement**: Folder `/resources/views/admin/update-fitur/` awalnya di-gitignore, tapi kemudian di-track di git setelah requirement berubah.
- **Font**: Menggunakan FontAwesome (sudah ter-load di layout) untuk icon bell, bukan Bootstrap Icons (tidak ter-load).
- **Style**: Bell menggunakan style yang konsisten dengan icon gear di sebelahnya (nav-link text-white p-0).