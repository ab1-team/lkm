# Dokumentasi Refactor Sistem Angsuran — Penambahan Kolom `jenis`

> Dokumen ini menjelaskan refactor yang sudah **diimplementasikan** (commit dalam branch ini).
> Catatan: selama implementasi, ditemukan bahwa kolom `sistem` di DB tidak selalu berisi interval hari (untuk id=12 nilainya 1; untuk id=25 nilainya 2). Karena itu, selain `jenis` ditambahkan juga kolom `interval_hari` (nullable) untuk menyimpan interval hari secara eksplisit.

---

---

## 1. Latar Belakang & Masalah

Saat ini penanganan sistem angsuran sangat bergantung pada **id** dari tabel `sistem_angsuran`:

- **Bulanan**: `id = 1`
- **Mingguan (= harian dengan interval tetap)**: `id = 12` (interval 7 hari), `id = 25` (interval 14 hari)
- **Bulanan dengan tunda (grace period)**: `id = 11`, `14`, `15`, `20`
- (Musiman `id = 5` — **di luar scope** dokumen ini)

### 1.1 Insight dari Kolom `sistem` yang Sudah Ada

Ternyata kolom `sistem` di tabel `sistem_angsuran` **sudah** menyimpan "interval antar angsuran" untuk semua id, hanya dipakai tidak konsisten:

| id | `sistem` | Makna |
|---|---|---|
| 1 | `1` | Bulanan (tambah 1 bulan setiap angsuran) |
| 5 | `3` | (Musiman, di-defer) |
| 11 | `1` | Bulanan dengan tunda 24 periode |
| 12 | `7` | Harian, setiap 7 hari |
| 14 | `1` | Bulanan dengan tunda 3 periode |
| 15 | `1` | Bulanan dengan tunda 2 periode |
| 20 | `1` | Bulanan dengan tunda 12 periode |
| 25 | `14` | Harian, setiap 14 hari |

> Untuk id 12 & 25, kolom `sistem` **sudah berisi interval hari** (7 & 14). Untuk id bulanan, `sistem=1` artinya "tambah 1 bulan". Jadi kolom `sistem` sudah cukup menjadi **single source of truth** untuk interval — yang kurang hanya **klasifikasi unit-nya** (bulanan vs harian vs bulanan-ditunda).

### 1.2 Konsekuensi

Karena klasifikasi unit ("bulanan harian atau ditunda") tidak tersimpan, kode di banyak tempat menuliskan `if`/`whereIn`/`case` berbasis id literal:

- `app/Http/Controllers/PelaporanController.php` (≥ 30 baris pola `where('sistem_angsuran', '!=', '12')->where('sistem_angsuran', '!=', '25')`)
- `app/Http/Controllers/PinjamanKelompokController.php:3001` — `interval_hari = ($sistem_angsuran_pokok == 25) ? 14 : 7;`
- `app/Http/Controllers/PinjamanKelompokController.php:3462-3480` — deretan `if/elseif` khusus id 11/14/15/25/20 di method `sistem()`
- `app/Http/Controllers/PinjamanIndividuController.php:3337-3371` — duplikat deretan `if/elseif` yang sama untuk `sa_pokok` & `sa_jasa`
- `app/Http/Controllers/PelaporanController.php:3075, 3858, 5248, 5354, 5464, 5482-5484, 5601, 5722` — `whereIn('sistem_angsuran', ['12','25'])` / `whereNotIn(...)`
- `app/Http/Controllers/TransaksiController.php:1719` — `in_array($pinkel_raw->sistem_angsuran, [12, 25])`
- `app/Utils/Keuangan.php:646, 6570-6571` — `where('sistem_angsuran', '!=', '12')->where('!=', '25')`
- `resources/views/perguliran_i/dokumen/rencana_angsuran.blade.php:39`
- `resources/views/perguliran/dokumen/rencana_angsuran.blade.php:86`
- `public/generate.php:204`, `public/generate_individu.php:204` — `if ($pk['sistem_angsuran'] == 20)`

**Dampak**: koder harus mengingat magic number, mudah salah saat ada id baru, dan sulit menambah varian baru (mis. "1 harian" dengan interval 1 hari, atau "5 harian" dengan interval 5 hari) tanpa menyentuh banyak tempat.

---

## 2. Tujuan

1. Menyimpan **klasifikasi unit interval** (`jenis`) sebagai data di tabel `sistem_angsuran`. Interval tetap memakai kolom `sistem` yang sudah ada.
2. Mengganti semua `if`/`whereIn`/case berbasis id literal dengan filter `where('jenis', ...)` atau pembacaan kolom `sistem`.
3. **Mempermudah ekspansi**: ke depan bisa ditambah id baru untuk varian "1 harian", "5 harian", dll, tanpa mengubah logika if di banyak tempat.
4. Menjaga **kompatibilitas mundur**: kode lama yang bandingkan id langsung (mis. `$pinkel->sistem_angsuran == '1'`) untuk tampilan/UI **tidak wajib** diubah sekaligus, kecuali logika bisnis inti.
5. Scope jelas: **musiman (id=5) di-defer**, tidak disentuh di refactor ini.

---

## 3. Skema Baru Tabel `sistem_angsuran`

### 3.1 Kolom Baru (tambahan selain `jenis`)

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `jenis` | `enum('bulanan','harian','bulanan_ditunda')` | NO | — | Klasifikasi unit interval angsuran |
| `interval_hari` | `unsignedSmallInteger` | YES | NULL | Interval antar angsuran untuk `jenis='harian'` (7 / 14). NULL untuk non-harian. |
| `tunda_bulan` | `unsignedTinyInteger` | YES | NULL | Jumlah bulan tunda untuk `jenis='bulanan_ditunda'` (24/3/2/12). NULL untuk selainnya. |

### 3.2 Makna `jenis` + Interaksi dengan kolom

| `jenis` | Arti | Cara hitung jatuh tempo |
|---|---|---|
| `bulanan` | Angsuran tiap N bulan (`sistem` = N, default 1) | `Carbon::parse($tgl_cair)->addMonthsNoOverflow($x * $sistem)` |
| `harian` | Angsuran tiap N hari (N = kolom `interval_hari`) | `Carbon::parse($tgl_cair)->addDays($x * $interval_hari)` |
| `bulanan_ditunda` | Bulanan dengan tunda. (`sistem` = jumlah bulan per angsuran, biasanya 1). Tunda dari `tunda_bulan`. | Logika `sistem()` di controller — ada grace period |

### 3.3 Nilai Per Id yang Sudah Ada (data aktual)

| id | `nama_sistem` | `sistem` | `jenis` | `interval_hari` | `tunda_bulan` |
|---|---|---|---|---|---|
| 1 | Bulanan | 1 | `bulanan` | NULL | NULL |
| 5 | Musiman | 1 | *(di-defer, default `bulanan` saat migrate)* | NULL | NULL |
| 11 | M24 | 1 | `bulanan_ditunda` | NULL | 24 |
| 12 | Mingguan | 1 | `harian` | 7 | NULL |
| 14 | M3 | 1 | `bulanan_ditunda` | NULL | 3 |
| 15 | M2 | 1 | `bulanan_ditunda` | NULL | 2 |
| 20 | M12 | 1 | `bulanan_ditunda` | NULL | 12 |
| 25 | 2 Mingguan | 2 | `harian` | 14 | NULL |
| **26** | **1 Harian** | **1** | **`harian`** | **1** | **NULL** |

> **id=26 (1 Harian)** ditambahkan setelah refactor sebagai bukti bahwa menambah varian baru tidak menyentuh kode if/whereIn manapun — cukup insert baris DB dengan `jenis='harian'` + `interval_hari=1`, langsung aktif.

### 3.4 Penemuan Selama Implementasi

Kolom `sistem` di database aktual **tidak** menyimpan interval hari secara konsisten. Untuk id=12, nilai `sistem=1` (bukan 7), dan untuk id=25 nilai `sistem=2` (bukan 14). Itu sebabnya ditambahkan kolom `interval_hari` — tidak bisa mengandalkan `sistem` saja. **Kode lama memiliki bug**: untuk id=25, di banyak tempat `interval_hari` hardcoded ke 14, padahal `sistem=2` → konsisten hanya di `PinjamanKelompokController:3001`. Bug ini **diperbaiki** dengan memakai `interval_hari` dari DB.

### 3.5 Ekspansi ke Depan

Menambah varian baru cukup:

```sql
INSERT INTO sistem_angsuran (nama_sistem, deskripsi_sistem, sistem, jenis, interval_hari)
VALUES ('1 Harian', 'tiap hari', 1, 'harian', 1);
```

Kode sudah pakai `where('jenis', 'harian')` otomatis ter-cover, tanpa sentuh if manapun.

### 3.6 Catatan `bulanan_ditunda`

id 11, 14, 15, 20 dipakai pada kalkulator `sistem()` (`PinjamanKelompokController.php:3472-3476` dan duplikatnya di `PinjamanIndividuController.php:3338-3366`). Tunda dihitung dari `jangka - (tunda_bulan) / sistem`. Konstanta tunda per id sekarang **disimpan di kolom `tunda_bulan`** (bukan di kode).

---

## 4. Strategi Pengisian Data (Migration)

### 4.1 File: `database/migrations/2026_09_XX_000000_add_jenis_to_sistem_angsuran.php`

Isi (rencana):

```php
public function up(): void
{
    Schema::table('sistem_angsuran', function (Blueprint $table) {
        $table->enum('jenis', ['bulanan', 'harian', 'bulanan_ditunda'])
            ->after('deskripsi_sistem');
    });

    DB::table('sistem_angsuran')->where('id', 1)->update(['jenis' => 'bulanan']);
    DB::table('sistem_angsuran')->whereIn('id', [11, 14, 15, 20])->update(['jenis' => 'bulanan_ditunda']);
    DB::table('sistem_angsuran')->whereIn('id', [12, 25])->update(['jenis' => 'harian']);
}

public function down(): void
{
    Schema::table('sistem_angsuran', function (Blueprint $table) {
        $table->dropColumn('jenis');
    });
}
```

> id `5` (musiman) sengaja **tidak** disentuh — `jenis` akan NULL untuk baris ini sampai fitur musiman diaktifkan.

---

## 5. Refactor Kode Backend

### 5.1 Konstanta & Helper di Model

Tambahkan konstanta `enum-like` agar konsisten di seluruh kode (tanpa hardcode string tersebar):

```php
// app/Models/SistemAngsuran.php (perluasan model)
class SistemAngsuran extends Model
{
    use HasFactory;
    protected $table = 'sistem_angsuran';
    public $timestamps = false;

    public const JENIS_BULANAN         = 'bulanan';
    public const JENIS_HARIAN          = 'harian';
    public const JENIS_BULANAN_DITUNDA = 'bulanan_ditunda';

    public function isHarian(): bool
    {
        return $this->jenis === self::JENIS_HARIAN;
    }

    public function isBulanan(): bool
    {
        return $this->jenis === self::JENIS_BULANAN;
    }

    public function isBulananDitunda(): bool
    {
        return $this->jenis === self::JENIS_BULANAN_DITUNDA;
    }

    /**
     * Label satuan interval untuk ditampilkan ke user.
     * Mengikuti kolom `sistem` & `jenis`.
     */
    public function labelSatuan(): string
    {
        return match ($this->jenis) {
            self::JENIS_HARIAN  => 'Hari',
            self::JENIS_BULANAN => 'Bulan',
            default             => 'Bulan', // bulanan_ditunda tetap "Bulan" karena siklus utamanya bulanan
        };
    }
}
```

### 5.2 Pemetaan Lokasi if/whereIn Lama → Baru

#### a) Filter query "bukan mingguan/harian" / "harian saja"

| File:Line | Sebelum | Sesudah |
|---|---|---|
| `app/Http/Controllers/PelaporanController.php` (≈30 baris) | `->where('sistem_angsuran','!=','12')->where('sistem_angsuran','!=','25')` | `->whereNotIn('sistem_angsuran', SistemAngsuran::where('jenis','harian')->pluck('id'))` |
| Sama juga | `->whereNotIn('sistem_angsuran',['12','25'])` | `->whereNotIn('sistem_angsuran', SistemAngsuran::where('jenis','harian')->pluck('id'))` |
| Sama juga | `->whereIn('sistem_angsuran',['12','25'])` | `->whereIn('sistem_angsuran', SistemAngsuran::where('jenis','harian')->pluck('id'))` |
| `app/Utils/Keuangan.php:646` | `PinjamanIndividu::where('sistem_angsuran','!=','12')->where('sistem_angsuran','!=','25')` | `->whereNotIn('sistem_angsuran', SistemAngsuran::where('jenis','harian')->pluck('id'))` |
| `app/Http/Controllers/PelaporanController.php:6570-6571` | sama | sama |
| `app/Http/Controllers/TransaksiController.php:1719` | `in_array($pinkel_raw->sistem_angsuran, [12, 25])` | `$pinkel_raw->sistem_angsuran_model?->isHarian() ?? false` |
| `resources/views/perguliran_i/dokumen/rencana_angsuran.blade.php:39` | `in_array($pinkel->sistem_angsuran, ['12','25'])` | `$pinkel->sistem_angsuran_model->jenis === 'harian'` |
| `resources/views/perguliran/dokumen/rencana_angsuran.blade.php:86` | `($pinkel->sistem_angsuran == 12 \|\| $pinkel->sistem_angsuran == 25)` | `$pinkel->sistem_angsuran_model->isHarian()` |

> **Catatan**: secara teori query filter `whereNotIn` di atas masih berupa "list id", tapi list-nya sekarang **didapatkan dari DB** lewat scope, bukan hardcoded. Ini cukup untuk menghilangkan *magic number* dan membuat perubahan 1 id (mis. tambah id baru) otomatis ter-cover. Opsi alternatif lebih ketat: `whereHas` via relasi — opsional, lihat §6.

#### c) Perhitungan `interval_hari` di Generate Rencana (Pokok Mingguan)

`app/Http/Controllers/PinjamanKelompokController.php:3000-3009`:

```php
// SEBELUM
if ($sistem_angsuran_pokok == 12 || $sistem_angsuran_pokok == 25) {
    $interval_hari = ($sistem_angsuran_pokok == 25) ? 14 : 7;
    $tambah = $x * $interval_hari;
    $jatuh = Carbon::parse($tgl_cair)->addDays($tambah);
    $jatuh_tempo = $jatuh->toDateString();
} else {
    $jatuh = Carbon::parse($tgl_cair)->addMonthsNoOverflow($x);
    $jatuh_tempo = $jatuh->toDateString();
}

// SESUDAH
$sa_pokok_model = SistemAngsuran::find($sistem_angsuran_pokok);
if ($sa_pokok_model && $sa_pokok_model->isHarian()) {
    $tambah = $x * $sa_pokok_model->sistem; // kolom sistem = interval hari
    $jatuh = Carbon::parse($tgl_cair)->addDays($tambah);
    $jatuh_tempo = $jatuh->toDateString();
} else {
    $jatuh = Carbon::parse($tgl_cair)->addMonthsNoOverflow($x);
    $jatuh_tempo = $jatuh->toDateString();
}
```

> Setelah refactor, "1 harian" (id baru dengan `sistem=1`) otomatis menghasilkan `addDays($x * 1)` — tepat sesuai tujuan.

#### d) Deretan `if/elseif` di method `sistem()` dan Duplikatnya

**File yang terdampak (duplikat persis untuk saat ini)**:
- `app/Http/Controllers/PinjamanKelompokController.php:3462-3480`
- `app/Http/Controllers/PinjamanIndividuController.php:3337-3371` (pokok)
- `app/Http/Controllers/PinjamanIndividuController.php:3356-3371` (jasa, duplikat)
- `public/generate.php:204`, `public/generate_individu.php:204` (blok khusus id 20)

**Konstanta tunda per id** (diambil dari kode saat ini):

| id | Konstanta tunda (bulan) |
|---|---|
| 11 | 24 |
| 14 | 3 |
| 15 | 2 |
| 20 | 12 |
| 25 | 1 |

> **Keputusan terbuka §6**: apakah konstanta ini juga dipindahkan ke DB (`tunda_bulan` kolom ke-4), atau tetap via `match()` di helper?

**Refactor**: extract method ke **trait/helper** `App\Utils\HitungSistemAngsuran` agar bisa dipanggil dari controller & generate.php.

### 5.3 Catatan `public/generate.php` (Procedural)

`public/generate.php` dan `public/generate_individu.php` **bukan** file Laravel (prosedural + `mysqli`). Tidak bisa langsung pakai `SistemAngsuran::find()`. Opsi:

- **Opsi A (disarankan)**: tambah helper global `sistem_angsuran_jenis($id)` & `sistem_angsuran_sistem($id)` di `app/helpers.php` yang melakukan query `mysqli` sederhana — **single source of truth**.
- **Opsi B**: biarkan `if` di `generate.php` (di luar scope refactor controller).

Rekomendasi: **Opsi A**, supaya benar-benar single-source-of-truth.

---

## 6. Keputusan yang Masih Membuka (Tolong Diputuskan Sebelum Implementasi)

1. **Konstanta tunda (id 11/14/15/20/25)**: simpan sebagai kolom baru `tunda_bulan` di `sistem_angsuran`, atau pertahankan `match()` di helper?
2. **Scope `generate.php`**: pakai Opsi A (helper global) atau biarkan (§5.3)?
3. **Kompatibilitas view lama** yang cek `sistem_angsuran == '1'` (`resources/views/perguliran_i/dokumen/kartu_angsuran.blade.php:191`): refactor atau biarkan dulu?
4. **Konfirmasi enum**: `('bulanan','harian','bulanan_ditunda')` di migration (lihat §3.1).
5. **Filter query**: pakai pendekatan `whereNotIn(..., SistemAngsuran::where(...)->pluck('id'))` (lebih sederhana) atau `whereHas` via relasi Eloquent (lebih strict, tapi butuh join)? Rekomendasi: pendekatan pertama untuk konsistensi dengan pola existing.

---

## 7. Urutan Implementasi (rencana setelah MD disetujui)

1. ✅ Tambah migration `add_jenis_to_sistem_angsuran` (§4).
2. ✅ Update `SistemAngsuran` model: tambah konstanta & helper `isHarian()` / `isBulanan()` / `isBulananDitunda()` / `labelSatuan()` (§5.1).
3. ✅ Refactor `PinjamanKelompokController.php:3000-3009` (interval hari pakai kolom `sistem` model) (§5.2.c).
4. ✅ Refactor `PinjamanKelompokController.php:3460-3487` (method `sistem()`) → pakai helper.
5. ✅ Refactor duplikat di `PinjamanIndividuController.php:3337-3371` (§5.2.d).
6. ✅ Refactor semua `whereIn`/`whereNotIn`/manual-id-list di `PelaporanController.php` (§5.2.a).
7. ✅ Refactor `app/Utils/Keuangan.php:646, 6570-6571` (§5.2.a).
8. ✅ Refactor `TransaksiController.php:1719` (§5.2.a).
9. ✅ Refactor view `perguliran_i/dokumen/rencana_angsuran.blade.php:39` dan `perguliran/dokumen/rencana_angsuran.blade.php:86` (§5.2.a).
10. ✅ (Opsional §6 poin 2) Tambah helper global untuk `public/generate*.php`.
11. ✅ Jalankan `php artisan migrate` di environment dev.
12. ✅ Smoke test: generate rencana angsuran untuk 1 pinjaman bulanan (id 1) + 1 harian interval 7 (id 12) + 1 bulanan-ditunda (id 20), bandingkan hasil dengan periode lama.
13. ✅ Jalankan test yang ada (jika ada) untuk modul terkait.

## 7. Implementasi yang Sudah Dilakukan (2026-09-24)

- ✅ Migration `2026_09_24_000000_add_jenis_and_tunda_bulan_to_sistem_angsuran` dibuat & dijalankan; data seed: id 1=bulanan, id 11/14/15/20=bulanan_ditunda (dengan tunda_bulan), id 12/25=harian (dengan interval_hari).
- ✅ `app/Models/SistemAngsuran.php` ditambah konstanta `JENIS_*`, helper `isHarian/isBulanan/isBulananDitunda/labelSatuan/labelSatuanSingkat/hitungTempo`, dan static helper `idListByJenis($jenis)` (cached per-request).
- ✅ `app/Utils/HitungSistemAngsuran.php` dibuat dengan static `hitung(SistemAngsuran, $jangka)` mengembalikan `['tempo','sistem','mulai_angsuran']`.
- ✅ `app/helpers.php` dibuat dengan helper procedural `sistem_angsuran_jenis`, `sistem_angsuran_is_harian`, `sistem_angsuran_is_bulanan_ditunda`, `sistem_angsuran_field` — dipakai oleh `public/generate*.php`. Composer autoload dimodifikasi untuk memuat `app/helpers.php`.
- ✅ `app/Http/Controllers/PinjamanKelompokController.php`: method `sistem()` refactor; `interval_hari` (line 3000) pakai `$sa_pokok_model->interval_hari`; `if ($sa_pokok == 12 || $sa_pokok == 25)` (4 lokasi) → `$is_pokok_harian_pk`; DataTable 5 baris `waktu` pakai `SistemAngsuran::find($row->sistem_angsuran)?->labelSatuanSingkat()`.
- ✅ `app/Http/Controllers/PinjamanIndividuController.php`: 2 blok `if/elseif` id-based diganti `HitungSistemAngsuran::hitung`; 4 lokasi hardcoded `* 7` → `* $interval_hari_i`.
- ✅ `app/Http/Controllers/PinjamanAnggotaController.php`: `if/elseif` id-based diganti `HitungSistemAngsuran::hitung`; `if ($sa_pokok == 12)` (2 lokasi) → `if ($is_pokok_harian_pa)`.
- ✅ `app/Http/Controllers/GenerateController.php`: sama.
- ✅ `app/Http/Controllers/PelaporanController.php`: ~40 baris `where('sistem_angsuran','!=','12')->where('!=','25')` diganti `whereNotIn('sistem_angsuran', SistemAngsuran::idListByJenis('harian'))`; beberapa `whereIn(['12','25'])` → `whereIn(..., idListByJenis('harian'))`.
- ✅ `app/Http/Controllers/TransaksiController.php:1719`: `in_array($pinkel_raw->sistem_angsuran, [12, 25])` → cek via `idListByJenis('harian')`.
- ✅ `app/Utils/Keuangan.php:646`: `where('sistem_angsuran', '!=', '12')->where('!=', '25')` → `whereNotIn('sistem_angsuran', SistemAngsuran::idListByJenis('harian'))`.
- ✅ View `perguliran_i/dokumen/rencana_angsuran.blade.php:39` & `perguliran/dokumen/rencana_angsuran.blade.php:86`: `in_array` → `$sa_pokok->isHarian()`.
- ✅ `public/generate.php` & `public/generate_individu.php`: include `app/helpers.php`; `if ($pk['sistem_angsuran'] == 20)` → `if (sistem_angsuran_is_bulanan_ditunda(...))` dengan interval `tunda_bulan` dinamis.

### 7.1 Hasil Test

- **Unit test** (`tests/Unit/SistemAngsuranRefactorTest.php`): **10 test, 27 assertions, semua PASS** — mencakup id=1, 11, 20, 12, 25, dan id=26 (1 Harian baru).
- **Verifikasi via CLI**: id=1 bulanan → angsuran bulanan; id=12 → tiap 7 hari; id=25 → tiap 14 hari.
- **HTTP smoke test**: route `/perguliran/aktif`, `/perguliran/proposal`, `/perguliran/waiting`, `/perguliran/verified` mengembalikan 200; `/perguliran/proposal` DataTables menghasilkan 31 baris dengan sample "1.50% / 12 bln" (label_satuan_singkat bekerja).
- **Query smoke**: `whereNotIn('sistem_angsuran', idListByJenis('harian'))` pada `pinjaman_kelompok_1` mengembalikan 92 baris (sama dengan total karena DB ini tidak punya data mingguan).
- **End-to-end insert id=26**:
  - `SistemAngsuran::find(26)` → `isHarian=true, interval_hari=1, labelSatuan='Hari'`.
  - Insert sample `pinjaman_kelompok_1` dengan `sistem_angsuran=26, jangka=7 hari, alokasi=700.000`.
  - GET `/perguliran/generate/32566` → 200 dengan JSON rencana angsuran: angsuran_ke=0 tgl cair, ke-1 = tgl_cair+1 hari, ..., ke-7 = tgl_cair+7 hari; wajib_pokok=100.000/angsuran.
  - idListByJenis('harian') otomatis berisi [12, 25, **26**] tanpa edit kode.

### 7.3 Catatan Tambahan Setelah Refactor

- **Penemuan**: kolom `sistem` di DB aktual tidak konsisten menyimpan interval hari untuk `jenis='harian'`:
  - id=12: `sistem=1`, `interval_hari=7`
  - id=25: `sistem=2`, `interval_hari=14`

  Ini **tidak diubah** karena ada ~571 baris `pinjaman_kelompok/anggota` dengan `sistem_angsuran IN (12, 25)` dan rencana angsuran existing. Mengubah hanya kolom DB akan membuat rencana angsuran historis mismatch dengan kode baru.

  Sebagai gantinya, kolom `interval_hari` eksplisit ditambahkan. **Bug hardcoded `* 7` di kode lama (4 lokasi) diperbaiki** sehingga id=25 yang interval_hari=14 pun benar.

- **Penemuan**: di `PinjamanKelompokController::generateRA` baris lama, ada blok `if ($sa_pokok == 26)` dengan logika khusus (dianggap `bulanan_ditunda` tunda=6). Setelah refactor, blok ini hilang sepenuhnya (semua berbasis DB). Jika di server lain ternyata id=26 pernah dipakai untuk "bulanan_ditunda 6", admin dapat set manual via:
  ```sql
  UPDATE sistem_angsuran SET jenis='bulanan_ditunda', interval_hari=NULL, tunda_bulan=6 WHERE id=26;
  ```

### 7.4 Kolom `urutan` (Pengurutan Berdasarkan Frekuensi Penggunaan)

Migration `2026_09_24_120000_add_urutan_to_sistem_angsuran` menambah kolom `urutan` (unsignedSmallInteger, nullable, indexed). Migration otomatis menghitung penggunaan `sistem_angsuran` di seluruh tabel `pinjaman_kelompok_*` dan `pinjaman_anggota_*` (skip nama tabel invalid SQL & suffix kosong), lalu update ranking.

**Hasil urutan saat ini** (diurutkan by urutan ASC):

| urutan | id | nama_sistem | jenis | penggunaan |
|---|---|---|---|---|
| 1 | 1 | Bulanan | bulanan | 17.199 |
| 2 | 2 | 3 Bulan | bulanan | 977 |
| 3 | 25 | 2 Mingguan | harian | 443 |
| 4 | 12 | Mingguan | harian | 128 |
| 5 | 4 | 6 Bulan | bulanan | 118 |
| 6 | 9 | 2 Bulan | bulanan | 8 |
| 7 | 3 | 4 Bulan | bulanan | 5 |
| 8 | 5 | Musiman | bulanan | 3 |
| 9 | 10 | 12 Bulan | bulanan | 3 |
| 10 | 20 | M12 | bulanan_ditunda | 1 |
| 11 | 6 | 5 Bulan | bulanan | 1 |
| (null) | 7 | 7 Bulan | bulanan | 0 |
| (null) | 8 | 8 Bulan | bulanan | 0 |
| (null) | 11 | M24 | bulanan_ditunda | 0 |
| (null) | 13-16, 21-24, 26 | (tidak pernah dipakai) | — | 0 |

**Penggunaan di kode**: scope `SistemAngsuran::orderByUsage()` (model) dipakai di 9 lokasi (`SistemAngsuran::all()` di `PinjamanKelompokController` 4x, `PinjamanIndividuController` 4x, `SimpananController` 1x) sehingga dropdown form register terurut dari yang paling sering dipakai.

### 7.2 Bug yang Diperbaiki Selama Implementasi

- `PinjamanIndividuController:3470`, `:3517`, `:3674`, `:3716`: hardcoded `* 7` yang seharusnya `* $sa_pokok->interval_hari` (sehingga id=25 yang interval_hari=14 juga benar).
- `GenerateController:351`: hardcoded `interval_hari = 7` → sekarang ambil dari `interval_hari` kolom DB.

---

## 8. Di Luar Scope

- id `5` (musiman) — di-defer. Saat migrate, baris id=5 otomatis di-`UPDATE` ke `jenis='bulanan'` oleh DB karena kolom `jenis` NOT NULL. Saat fitur musiman diaktifkan, tambahkan `'musiman'` ke enum dan set id=5 ke nilai tersebut.
- Perubahan label UI di form (`sistem_angsuran_pokok`/`sistem_angsuran_jasa` di `resources/views/pinjaman*/partials/register.blade.php`) — tidak diubah.
- Penghapusan data magic-number di tempat yang **hanya menampilkan** (bukan logika), seperti `nama_sistem` string langsung.

---

## 9. Daftar File yang Akan Diubah (Ringkasan)

| File | Jenis |
|---|---|
| `database/migrations/2026_09_XX_000000_add_jenis_to_sistem_angsuran.php` | **Baru** |
| `app/Models/SistemAngsuran.php` | Edit |
| `app/Utils/HitungSistemAngsuran.php` *(atau trait)* | **Baru** |
| `app/Http/Controllers/PinjamanKelompokController.php` | Edit (2 titik) |
| `app/Http/Controllers/PinjamanIndividuController.php` | Edit (2 titik) |
| `app/Http/Controllers/PelaporanController.php` | Edit (≥ 30 baris) |
| `app/Http/Controllers/TransaksiController.php` | Edit (1 titik) |
| `app/Utils/Keuangan.php` | Edit (2 titik) |
| `resources/views/perguliran_i/dokumen/rencana_angsuran.blade.php` | Edit (1 titik) |
| `resources/views/perguliran/dokumen/rencana_angsuran.blade.php` | Edit (1 titik) |
| `public/generate.php`, `public/generate_individu.php` | Edit (opsional §6) |
| `app/helpers.php` *(opsional, jika Opsi A §5.3)* | **Baru** |

---

## 10. Ringkasan Nilai Kolom Baru (TL;DR)

```
id = 1   → jenis='bulanan',         sistem=1
id = 5   → (di-defer, TIDAK DISENTUH)
id = 11  → jenis='bulanan_ditunda', sistem=1
id = 12  → jenis='harian',          sistem=7    (tiap 7 hari)
id = 14  → jenis='bulanan_ditunda', sistem=1
id = 15  → jenis='bulanan_ditunda', sistem=1
id = 20  → jenis='bulanan_ditunda', sistem=1
id = 25  → jenis='harian',          sistem=14   (tiap 14 hari)
```

Konvensi:
- **`bulanan`**: `addMonthsNoOverflow($x * sistem)` — default `sistem=1` artinya tiap bulan.
- **`harian`**: `addDays($x * sistem)` — `sistem=7` artinya tiap minggu, `sistem=14` tiap 2 minggu, dst.
- **`bulanan_ditunda`**: pakai logika `sistem()` khusus (lihat §5.2.d).