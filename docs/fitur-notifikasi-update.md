# Fitur Notifikasi "Update Fitur" — Dokumentasi Implementasi Laravel

Dokumentasi ini dibuat **universal**, artinya bisa dipasang di beberapa aplikasi Laravel (POS, sidbm_baru, siupk, dst) tanpa perlu ubah struktur besar. Cukup salin file-file yang disebutkan, sesuaikan namespace/prefix bila perlu.

---

## 1. Ringkasan Fitur

| # | Fitur | Keterangan |
|---|-------|------------|
| 1 | Form input (khusus lokal) | Untuk menambah/mengedit isi `update_fitur`, hanya bisa diakses saat aplikasi jalan di environment lokal |
| 2 | Icon lonceng notifikasi | Ditaruh di layout utama (header), menampilkan badge jumlah belum dibaca |
| 3 | Tombol "Tandai sudah dibaca" | Menyimpan status baca via **cookie** |
| 4 | Notifikasi otomatis hilang setelah 7 hari | Item lama tetap ada di DB & di halaman timeline, tapi tidak lagi muncul di dropdown lonceng |
| 5 | Halaman timeline semua update | Menampilkan seluruh riwayat `update_fitur`, tanpa batas 7 hari |

Tabel `update_fitur` **sudah ada** di database — tidak perlu migration/seeder baru:

```
id        (PK, auto increment)
tanggal   datetime
judul     varchar(30)
deskripsi text
foto      text
jenis     -- lihat poin 2
```

---

## 2. Definisi Kolom `jenis`

Karena kolom `jenis` diserahkan ke saya, saya sarankan menyimpannya sebagai **string bebas** (tidak perlu ALTER TABLE ke enum DB — cukup divalidasi di level aplikasi/Laravel supaya tetap fleksibel lintas project). Nilai yang disarankan:

| Value (disimpan di DB) | Label tampilan | Warna badge (opsional) |
|---|---|---|
| `fitur` | Fitur Baru | primary / biru |
| `perbaikan` | Perbaikan Bug | warning / kuning |
| `pengumuman` | Pengumuman | info / cyan |
| `maintenance` | Maintenance | secondary / abu |

Validasi di controller cukup pakai `Rule::in(['fitur','perbaikan','pengumuman','maintenance'])`. Kalau ke depan mau nambah jenis baru, tinggal tambah di satu tempat (lihat `config/update_fitur.php` di bawah).

---

## 3. Struktur File yang Dibuat

```
app/
  Models/
    UpdateFitur.php
  Http/
    Controllers/
      Admin/UpdateFiturController.php      <- form input (local only)
      UpdateFiturController.php            <- notifikasi + timeline (publik/user)
    Middleware/
      LocalOnly.php
config/
  update_fitur.php                         <- daftar "jenis" & masa berlaku notif
resources/
  views/
    admin/
      update-fitur/
        index.blade.php                    <- (GITIGNORE)
        form.blade.php                     <- (GITIGNORE)
    update-fitur/
      timeline.blade.php                   <- halaman semua timeline
    components/
      notif-bell.blade.php                 <- partial icon lonceng, di-include di layout
routes/
  web.php                                  <- tambahan route
public/js/
  notif-bell.js                            <- AJAX mark as read
```

---

## 4. Model

`app/Models/UpdateFitur.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UpdateFitur extends Model
{
    protected $table = 'update_fitur';
    public $timestamps = false; // tabel sudah ada, tidak punya created_at/updated_at

    protected $fillable = [
        'tanggal', 'judul', 'deskripsi', 'foto', 'jenis',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    /** Hanya update dalam N hari terakhir (untuk dropdown notifikasi) */
    public function scopeDalamMasaNotif(Builder $query): Builder
    {
        $hari = config('update_fitur.masa_berlaku_hari', 7);
        return $query->where('tanggal', '>=', now()->subDays($hari));
    }

    public function scopeTerbaruDulu(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal');
    }
}
```

---

## 5. Config (opsional tapi memudahkan reuse di project lain)

`config/update_fitur.php`

```php
<?php

return [
    // notifikasi otomatis hilang dari lonceng setelah sekian hari
    'masa_berlaku_hari' => 7,

    'jenis' => [
        'fitur'       => ['label' => 'Fitur Baru',     'badge' => 'primary'],
        'perbaikan'   => ['label' => 'Perbaikan Bug',  'badge' => 'warning'],
        'pengumuman'  => ['label' => 'Pengumuman',     'badge' => 'info'],
        'maintenance' => ['label' => 'Maintenance',    'badge' => 'secondary'],
    ],

    // nama cookie penanda "sudah dibaca sampai kapan"
    'cookie_name' => 'notif_update_fitur_read_at',
    'cookie_days' => 365,
];
```

---

## 6. Status "Sudah Dibaca" — Pakai Cookie (paling simpel)

Karena aplikasi ini kemungkinan dipakai lintas project dan sebagian tidak selalu punya sesi user yang seragam, **cookie** lebih portable dibanding cache server (tidak perlu key unik per user/session, otomatis per-browser, tidak butuh storage tambahan di server).

**Logika:**
- Cookie `notif_update_fitur_read_at` menyimpan timestamp terakhir user menekan "Tandai sudah dibaca".
- **Unread count** = jumlah baris `update_fitur` dengan `tanggal >= now()-7hari` **dan** `tanggal > cookie(read_at)`.
- Jika cookie belum ada sama sekali → anggap semua notifikasi dalam 7 hari terakhir **belum dibaca**.
- Saat tombol "Tandai sudah dibaca" ditekan → cookie di-set ke `now()`, umur cookie dibuat panjang (365 hari) supaya tidak sering ke-reset.

Ini otomatis menyelesaikan requirement no. 4 juga: begitu `tanggal` sebuah item lewat dari 7 hari, item itu keluar dari query notifikasi (walaupun cookie tidak diubah), tapi tetap muncul di halaman timeline karena timeline tidak pakai filter 7 hari.

---

## 7. Controller — Notifikasi (bell + timeline)

`app/Http/Controllers/UpdateFiturController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\UpdateFitur;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UpdateFiturController extends Controller
{
    private function readAt(Request $request): ?Carbon
    {
        $cookieName = config('update_fitur.cookie_name');
        $value = $request->cookie($cookieName);
        return $value ? Carbon::parse($value) : null;
    }

    /** Dipanggil dari partial lonceng (bisa via view composer atau AJAX) */
    public function dropdown(Request $request)
    {
        $items = UpdateFitur::dalamMasaNotif()->terbaruDulu()->get();
        $readAt = $this->readAt($request);

        $unreadCount = $readAt
            ? $items->where('tanggal', '>', $readAt)->count()
            : $items->count();

        return response()->json([
            'items'        => $items,
            'unread_count' => $unreadCount,
        ]);
    }

    /** Tombol "Tandai sudah dibaca" */
    public function tandaiDibaca(Request $request)
    {
        $cookieName = config('update_fitur.cookie_name');
        $days = config('update_fitur.cookie_days', 365);

        return back()->withCookie(
            cookie($cookieName, now()->toIso8601String(), 60 * 24 * $days)
        );
    }

    /** Halaman semua timeline (tanpa batas 7 hari) */
    public function timeline()
    {
        $items = UpdateFitur::terbaruDulu()->paginate(15);
        return view('update-fitur.timeline', compact('items'));
    }
}
```

---

## 8. Routes

`routes/web.php`

```php
use App\Http\Controllers\UpdateFiturController;
use App\Http\Controllers\Admin\UpdateFiturController as AdminUpdateFiturController;

// --- Notifikasi (user yang sudah login) ---
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi/dropdown', [UpdateFiturController::class, 'dropdown'])->name('notif.dropdown');
    Route::post('/notifikasi/tandai-dibaca', [UpdateFiturController::class, 'tandaiDibaca'])->name('notif.tandaiDibaca');
    Route::get('/notifikasi/timeline', [UpdateFiturController::class, 'timeline'])->name('notif.timeline');
});

// --- Admin input (LOCAL ONLY) ---
Route::middleware('local.only')->prefix('admin/update-fitur')->name('admin.updateFitur.')->group(function () {
    Route::resource('/', AdminUpdateFiturController::class)->parameters(['' => 'update_fitur']);
});
```

---

## 9. Middleware "Local Only"

Saya sarankan cek `APP_ENV=local` di middleware — ini paling portable karena tidak tergantung IP hosting (yang bisa berubah-ubah per server/ISP), cukup pastikan `.env` di server produksi **tidak** diset `APP_ENV=local`.

`app/Http/Middleware/LocalOnly.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            abort(404); // disamarkan sebagai "halaman tidak ada", bukan 403
        }

        return $next($request);
    }
}
```

Daftarkan di `bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'local.only' => \App\Http\Middleware\LocalOnly::class,
    ]);
})
```

atau di `app/Http/Kernel.php` (Laravel ≤10) pada `$middlewareAliases`.

> Kalau nanti butuh lapis tambahan (misal kadang mau tes di server staging), tinggal tambah pengecekan IP di middleware yang sama.

---

## 10. Form Input Admin (di-gitignore)

`app/Http/Controllers/Admin/UpdateFiturController.php` — CRUD standar (index, create, store, edit, update, destroy) ke model `UpdateFitur`. Yang penting **view**-nya di-ignore dari git supaya:
- Tetap bisa dipakai untuk kerja lokal (karena file fisiknya tetap ada di disk kamu),
- Tidak ikut ter-commit/ter-push ke server produksi (jadi kalaupun middleware `local.only` gagal, halaman fisiknya memang tidak ada di server).

`foto` diupload sebagai file gambar, dan **yang disimpan ke kolom `foto` hanya nama filenya** (bukan path lengkap, bukan base64). File fisik disimpan lewat Laravel Storage (disk `public`), supaya bisa diakses via symlink `public/storage`.

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\UpdateFitur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UpdateFiturController extends Controller
{
    private const FOTO_DIR = 'update-fitur'; // storage/app/public/update-fitur

    public function index()
    {
        $items = UpdateFitur::terbaruDulu()->paginate(10);
        return view('admin.update-fitur.index', compact('items'));
    }

    public function create()
    {
        return view('admin.update-fitur.form', ['item' => new UpdateFitur()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['foto'] = $this->simpanFoto($request);

        UpdateFitur::create($data);

        return redirect()->route('admin.updateFitur.index')->with('status', 'Update fitur ditambahkan.');
    }

    public function edit(UpdateFitur $update_fitur)
    {
        return view('admin.update-fitur.form', ['item' => $update_fitur]);
    }

    public function update(Request $request, UpdateFitur $update_fitur)
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto')) {
            $this->hapusFotoLama($update_fitur->foto);
            $data['foto'] = $this->simpanFoto($request);
        } else {
            unset($data['foto']); // tidak ganti foto kalau tidak upload baru
        }

        $update_fitur->update($data);

        return redirect()->route('admin.updateFitur.index')->with('status', 'Update fitur diperbarui.');
    }

    public function destroy(UpdateFitur $update_fitur)
    {
        $this->hapusFotoLama($update_fitur->foto);
        $update_fitur->delete();

        return redirect()->route('admin.updateFitur.index')->with('status', 'Update fitur dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal'    => ['required', 'date'],
            'judul'      => ['required', 'string', 'max:30'],
            'deskripsi'  => ['required', 'string'],
            'jenis'      => ['required', Rule::in(array_keys(config('update_fitur.jenis')))],
            'foto'       => [$request->isMethod('post') ? 'required' : 'nullable', 'image', 'max:2048'],
        ]);
    }

    private function simpanFoto(Request $request): string
    {
        $file = $request->file('foto');
        $filename = $file->hashName(); // nama unik otomatis, mis: 63f1...9a.jpg
        $file->storeAs(self::FOTO_DIR, $filename, 'public');

        return $filename; // hanya nama file yang disimpan ke DB
    }

    private function hapusFotoLama(?string $filename): void
    {
        if ($filename) {
            Storage::disk('public')->delete(self::FOTO_DIR . '/' . $filename);
        }
    }
}
```

> Jalankan `php artisan storage:link` sekali di tiap project (kalau belum pernah) supaya `public/storage` ter-symlink ke `storage/app/public`.

Di form (`resources/views/admin/update-fitur/form.blade.php`), pastikan tag `<form>` pakai `enctype="multipart/form-data"` dan inputnya `<input type="file" name="foto">`.

### Tambahan di `.gitignore`

```gitignore
# Form input notifikasi (khusus lokal, tidak ikut deploy)
/resources/views/admin/update-fitur/
```

Buat filenya secara manual di lokal (tidak akan ke-track git setelah baris di atas ditambahkan **sebelum** file pertama kali di-add):

```
resources/views/admin/update-fitur/index.blade.php
resources/views/admin/update-fitur/form.blade.php
```

Isinya bebas — form biasa dengan field `tanggal`, `judul` (max 30), `deskripsi` (textarea), `foto` (input file atau text path), dan `jenis` (select dari `config('update_fitur.jenis')`).

> ⚠️ Karena sudah ke-gitignore, ingat untuk **backup manual** file form ini (misal simpan salinan di luar repo) kalau ganti komputer, karena git tidak akan menyimpannya.

---

## 11. Komponen Lonceng Notifikasi

`resources/views/components/notif-bell.blade.php`

```blade
<div class="dropdown" id="notif-bell-wrapper">
    <button class="btn btn-link position-relative" id="notif-bell-btn" data-bs-toggle="dropdown">
        <i class="bi bi-bell fs-5"></i>
        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle d-none"
              id="notif-badge">0</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-2" style="width: 320px;" id="notif-list">
        <div class="text-center text-muted small py-3">Memuat...</div>
    </div>
</div>
```

Include di layout utama (misal `layouts/app.blade.php`) di bagian header/navbar — bungkus dengan `@auth` karena notifikasinya khusus user yang sudah login:

```blade
@auth
    <x-notif-bell />
@endauth
```

(Kalau project belum pakai Blade components, ganti jadi `@include('components.notif-bell')`.)

---

## 12. JS — Ambil Data, Render, Tandai Dibaca

`public/js/notif-bell.js`

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('notif-list');
    const badge = document.getElementById('notif-badge');
    if (!list) return;

    function muatNotifikasi() {
        fetch('/notifikasi/dropdown')
            .then(res => res.json())
            .then(data => {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                if (data.items.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted small py-3">Belum ada update</div>';
                    return;
                }

                let html = data.items.slice(0, 5).map(item => `
                    <div class="small border-bottom py-2">
                        <strong>${item.judul}</strong>
                        <div class="text-muted" style="font-size:.75rem">${item.tanggal}</div>
                    </div>
                `).join('');

                html += `
                    <div class="d-flex justify-content-between mt-2">
                        <a href="/notifikasi/timeline" class="small">Lihat semua</a>
                        <button type="button" class="btn btn-sm btn-link small p-0" id="btn-tandai-dibaca">Tandai sudah dibaca</button>
                    </div>
                `;

                list.innerHTML = html;

                document.getElementById('btn-tandai-dibaca')?.addEventListener('click', tandaiDibaca);
            });
    }

    function tandaiDibaca() {
        fetch('/notifikasi/tandai-dibaca', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        }).then(() => muatNotifikasi());
    }

    muatNotifikasi();
});
```

Pastikan layout punya `<meta name="csrf-token" content="{{ csrf_token() }}">` dan load script ini:

```blade
<script src="{{ asset('js/notif-bell.js') }}"></script>
```

---

## 13. Halaman Timeline Semua Update

`resources/views/update-fitur/timeline.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-3">Riwayat Pembaruan</h4>

    @foreach ($items as $item)
        <div class="card mb-3">
            <div class="card-body">
                <span class="badge bg-{{ config("update_fitur.jenis.{$item->jenis}.badge", 'secondary') }}">
                    {{ config("update_fitur.jenis.{$item->jenis}.label", $item->jenis) }}
                </span>
                <h5 class="mt-2">{{ $item->judul }}</h5>
                <div class="text-muted small mb-2">{{ $item->tanggal->translatedFormat('d F Y, H:i') }}</div>
                <p>{{ $item->deskripsi }}</p>
                @if ($item->foto)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url('update-fitur/' . $item->foto) }}"
                         class="img-fluid rounded" alt="{{ $item->judul }}">
                @endif
            </div>
        </div>
    @endforeach

    {{ $items->links() }}
</div>
@endsection
```

---

## 14. Cara Pakai di Project Laravel Lain

Karena tabel `update_fitur` katanya sudah ada di masing-masing DB, langkah reuse tinggal:

1. Copy: `Model`, `Controller` (2 file), `Middleware`, `config/update_fitur.php`, `views/components/notif-bell.blade.php`, `views/update-fitur/timeline.blade.php`, `public/js/notif-bell.js`.
2. Tambahkan baris route & middleware alias.
3. Tambahkan `<x-notif-bell />` + `<script src="...notif-bell.js">` di layout.
4. Tambahkan baris `.gitignore` untuk folder form admin, lalu buat form-nya manual per project (karena tampilan admin tiap project biasanya beda framework CSS-nya — Bootstrap 5, Soft UI, dll).
5. Sesuaikan `jenis` di `config/update_fitur.php` kalau project tertentu butuh kategori berbeda.

---

## 15. Catatan / Hal yang Perlu Diperhatikan Saat Implementasi

- **Field `foto`**: sudah dipastikan berupa **upload file gambar**, kolom `foto` di DB hanya menyimpan **nama file** — implementasinya ada di §10 (upload ke `storage/app/public/update-fitur`, diakses via `php artisan storage:link`).
- **Autentikasi**: rute `/notifikasi/*` (dropdown, tandai dibaca, timeline) dibungkus middleware `auth` — hanya user yang sudah login yang bisa lihat lonceng & timeline. Form admin (`/admin/update-fitur/*`) tidak butuh `auth` sama sekali karena sudah dijaga cukup dengan `local.only` (dan tidak ikut ter-deploy karena view-nya di-gitignore).
- **Polling otomatis**: saat ini badge hanya refresh saat halaman dimuat. Kalau mau real-time, bisa tambah `setInterval(muatNotifikasi, 60000)` di JS.
