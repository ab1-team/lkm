# Laporan OJK — NIK, Flattening, Pemisahan Kolom

Tiga aturan wajib untuk seluruh laporan OJK. Detail dan gotcha:
lihat bagian bawah file ini.

## 1. Format NIK / No. Identitas (WAJIB TEKS)

Excel hanya menyimpan 15 digit signifikan (IEEE 754 double). Bila NIK ditulis
sebagai number, digit ke-16 dibulatkan menjadi 0:

```
3305051104800002  ->  330505110480000
```

Jalur HTML -> `.xls` **tidak bisa** mengikat tipe sel, sehingga NIK selalu
bermasuk sebagai angka. Solusinya: `app/Utils/ExcelExporter.php` mendeteksi
kolom identitas dari baris `<th>`, lalu mengikat nilainya dengan

```php
setCellValueExplicitByColumnAndRow($col, $row, $value, DataType::TYPE_STRING)
// + number format '@' (Text)
```

NIK bernilai NULL/kosong menjadi string kosong `""` — bukan `0`, bukan `-`,
dan tipe datanya tetap string.

Kolom yang dikenali: `NIK`, `No. KTP`, `No. Identitas`, `CIF / NO. ANGGOTA`,
`NO. ANGGOTA`, `NO. REKENING`, `LOAN ID`, `ID PINJAMAN`, `KODE ANGGOTA`, dan
varian serupa. Lihat `ExcelExporter::TEXT_COLUMNS`.

`laporan` = 20 (kecamatan) dan 21 (kabupaten) sekarang diekspor sebagai
`.xlsx` native lewat exporter tersebut. Laporan lain tetap jalur HTML lama.

## 2. Flattening & Sorting (TANPA PENGELOMPOKAN PER DESA / PER PRODUK)

- Tidak ada baris judul desa.
- Tidak ada subtotal `Jumlah <desa>`.
- Loan diurutkan **Tanggal Pencairan** (`tgl_cair`) ascending, tie-breaker `id`.
- Simpanan diurutkan **Tanggal Penyimpanan** (`tgl_buka`) ascending, tie-breaker `id`.
- Pengelompokan per produk pinjaman **dan** per jenis simpanan juga dihapus supaya
  urutan tidak terpotong.

### Cara implementasinya

Ordering alone tidak cukup. Query memuat relasi per produk
(`JenisProdukPinjaman->pinjaman_individu`, `JenisSimpanan->simpanan`) sehingga
controller mengurutkan **di dalam** tiap produk. View yang tetap loop per produk
lalu membuat tanggal "reset" di tiap batas produk — urutan global tetap salah
walaupun tiap bagian benar urut. Karena itu flatten harus dilakukan di view:

```php
// di dalam view, sebelum <table>
$pinjaman_gabungan = collect();
foreach ($jenis_pp as $jpp) {
    foreach ($jpp->pinjaman_individu as $pinj_i) {
        $pinj_i->nama_jpp = $jpp->nama_jpp;   // simpan label produk per baris
        $pinjaman_gabungan->push($pinj_i);
    }
}
$pinjaman_gabungan = $pinjaman_gabungan->sortBy([
    ['tgl_cair', 'asc'], ['id', 'asc'],
])->values();
```

Laporan yang memang tidak punya kolom identitas (`neraca_ojk`, `labarugi`,
`max_suku_bunga`, `penempatan_dana`, `cover_o`, `SEOJK_PTK19/41`, `PTK_POJK`)
tidak perlu flatten — aturan 2 tidak berlaku di sana.

## 3. Kapitalisasi Nama & Pemisahan Kolom

Nama dicetak KAPITAL: `{{ strtoupper($x->namadepan) }}`.

Nama **dilarang** digabung dengan Loan-ID dalam satu sel
(`BUDI SANTOSO - LN-2026-000123`). Wajib kolom terpisah:

| Kolom | Contoh |
|---|---|
| `LOAN ID` | `LN-2026-000123` |
| `NAMA DEBITUR` | `BUDI SANTOSO` |
| `CIF / NO. ANGGOTA` | `MBR-001234` |
| `NIK` | `3273010101010001` |

Untuk laporan simpanan gunakan `NAMA PENYIMPAN`; untuk kelompok pakai
`NAMA KELOMPOK`.

Karena produk sudah digabung ke satu tabel, kolom label produk **wajib** ikut
dibawa sebagai kolom biasa (`Jenis Simpanan` di DRT/SMPN, `Jenis Penggunaan`
di `pinjaman_diberi`) — kalau tidak, baris jadi tidak bisa dikaitkan ke produk.

Setelah menambah kolom, hitung ulang **seluruh** `colspan` di tabel tersebut:
baris header, baris body, subtotal, baris total, dan tabel nested.

## Gotcha

1. `rincian_pinjaman_diterima` pernah kehilangan `@endforeach` — gejalanya
   "unexpected end of file". Blade tidak selalu menangkap, PHP yang menagli.
2. `$y12` di `daftar_rincian_tabungan` hanya di-set di dalam loop padahal
   dipakai di footer. Loop kosong -> undefined variable.
3. Setelah flatten, variabel per-desa (`$j_*`, `$a_*`) yang tadinya di-reset
   per desa harus diinisialisasi ulang **sekali di atas loop**, kalau tidak
   error "Undefined variable $j_alokasi".
4. `pinjaman_diberi` punya `select()` berisi `'.nik'` (tanpa prefix tabel) —
   SQL invalid, dan baru ketahuan saat kolomnya benar-benar dipakai.
5. `number_format()` menolak string. JSON agunan bisa berisi nilai numerik
   sebagai string, jadi selalu `(float)` cast sebelum `number_format()`.
   Gejalanya: `number_format(): Argument #1 ($num) must be of type int|float`.
6. `kolekbilitas_pinjaman2` dipakai oleh **dua** controller: `KBP` mengirim
   `$jenis_pp` (relasi `pinjaman_anggota`), `KBP2` mengirim `$jenis_pp_i`
   (relasi `pinjaman_individu`). View harus terima keduanya:

   ```php
   $daftar_i      = $jenis_pp_i ?? null;
   $daftar_produk = $daftar_i ?? ($jenis_pp ?? collect());
   $relasi        = $daftar_i !== null ? 'pinjaman_individu' : 'pinjaman_anggota';
   ```

   Nama variabel PINDAH-PINDAH antar controller adalah sumber bug yang sama.
   Cek selalu "siapa saja yang me-render view ini".

7. Flatten memindahkan variabel accumulator. Kalau `$k_alokasi` / `$nilai_agunan`
   dihitung di dalam perulangan yang dihapus, nilainya hilang — dan view gagal
   dengan "Undefined variable", bukan diam-diam angka salah. Semua accumulator
   harus di-deklarasikan sekali di atas tabel.

8. Jangan ikut mengubah query di luar lingkup tugas. `pinjaman_individu`,
   `SEOJK_KBP19`, dan `SEOJK_KBP41` juga punya `orderBy(...desa...)`, tetapi
   view-nya masih mengelompokkan per desa. Menghapus orderBy-nya di sana
   memecahkan urutan tanpa manfaat.

9. `$data['jabatan']` / `$data['level']` **tidak pernah diisi** oleh
   `preview()` — dia menyimpan keduanya sebagai variabel lokal (`$jabatan`,
   `$level`) lalu hanya menaruh `$data['dir']`. `PF()` pernah membaca
   `$data['jabatan']` dan meledak `Undefined array key "jabatan"` di 31 lokasi.
   Perhatikan selalu: kalau sebuah report method membaca key `$data` yang
   tidak di-set `preview()`, itu bug — bukan data yang bermasalah.
   Pengecualian per-lokasi seperti `if (lokasi == 362)` jangan dipindahkan ke
   view sebagai penentu logic; pakai nilai sebenarnya (`jabatan == 1`).

10. Data tabular tidak selalu numerik. `saham.rp_saham` bisa tersimpan sebagai
    string berformat (`'499.000.000'`, `'0,5%'`) sehingga `number_format()`
    melempar "A non-numeric value encountered". Bersihkan dulu:
    `str_replace(',', '.', rtrim($v, '%'))`.

11. Laporan yang dirender untuk satu lokasi saja bisa menyamar benar di
    lokasi itu. `PF()` punya cabang khusus `362` sehingga error-nya tidak
    muncul di sana. **Scan semua lokasi** saat memperbaiki laporan profil:

    ```php
    foreach (Kecamatan::all() as $kec) { /* invoke PF, catat yang gagal */ }
    ```

## Verifikasi

```bash
# test NIK (unit exporter + NIK dari DB nyata)
./vendor/bin/phpunit tests/Feature/NikIdentityTest.php
./vendor/bin/phpunit tests/Feature/OjkExportEndToEndTest.php

# kompilasi semua view pelaporan + cek paritas kolom header vs body
find resources/views/pelaporan/view -name '*.blade.php' -exec php -l {} \;
php artisan view:cache     # kompilasi blade sungguhan, bukan cuma php -l
```

Untuk paritas kolom, compile tiap view lalu bandingkan jumlah `<th>`/`<td>`
header dengan baris body — dan bandingkan hasilnya dengan baseline (stash
perubahan dulu). Beberapa view sudah punya ketidaksesuaian kolom sejak awal,
jadi yang penting adalah **tidak menambah** yang baru.

`php -l` tidak cukup: Blade lolos `php -l` tetapi masih bisa gagal saat
render (undefined variable dari view). Verifikasi terakhir harus benar-benar
invoke controller method terhadap data DB.

Dua kegagalan lama di `SistemAngsuranRefactorTest` sudah ada sebelum
perubahan ini dan bukan regresi.

## Status per laporan (terakhir diverifikasi)

Semua sudah selesai. Angka = jumlah baris dari render nyata (lokasi 109,
tahun berjalan), bukan asumsi.

| Method | View | Flatten | Kolom identitas | NIK 16 digit |
|---|---|---|---|---|
| DRP | ojk/daftar_rincian_pinjamanaktif | ✅ | ✅ | ✅ 73 |
| DRPL | ojk/rincian_pinjaman_lunas | ✅ | ✅ | ✅ |
| DRPA | ojk/daftar_rincian_pinjamanagunan | ✅ | ✅ | ✅ 73 |
| DRT | ojk/daftar_rincian_tabungan | ✅ | ✅ | ✅ 72 |
| DRS | ojk/fd_rincian_simpanan | ✅ | ✅ | ✅ 77 |
| SMPN | ojk/simpanan_piutang | ✅ | ✅ | ✅ 72 |
| DRPY | ojk/rincian_pinjaman_diterima | ✅ | ✅ | ✅ 77 |
| KBP / KBP2 | ojk/kolekbilitas_pinjaman2 | ✅ | ✅ | ✅ 77 |
| pinjaman_diberi | ojk/pinjaman_diberi | ✅ | ✅ | ✅ 77 |
| piutang | ojk/piutang | ✅ | ✅ | ✅ 77 |
| piutang_gabungan | ojk/piutang_gabungan | ✅ | ✅ | ✅ 77 |
| pcpp | ojk/penyisihan_cadangan | ✅ | n/a (rekap) | n/a |
| SEOJK_KBP19/41 | seojk/kolekbilitas_pinjaman_19/41 | ✅ | ✅ | ✅ |
| SEOJK_PTK19/41, PTK_POJK | seojk/, pojk/ | n/a | n/a | n/a |

Catatan:
- `ojk/kolekbilitas_pinjaman.blade.php` **tidak dipakai** controller mana pun
  (view yang aktif adalah `kolekbilitas_pinjaman2` untuk KBP dan KBP2).
- `DRP`/`DRPA` masih satu tabel per jenis produk; flatten per desa sudah
  terkonfirmasi lewat DB (77 baris utuh, 21 desa, urut `tgl_cair`+`id`).
- Penghapusan `orderBy(...desa...)` sengaja hanya di 6 method OJK
  (`DRP`, `DRPL`, `DRPA`, `KBP2`, `pinjaman_diberi`, `piutang`,
  `piutang_gabungan`). Jangan ikut mengubah method lain tanpa flattening
  view-nya.

## PF / Profil OJK

Terpisah dari tiga aturan di atas, tapi sering ikut disentuh bersamaan.
`profil_o.blade.php` + `PF()`. Semua **32 lokasi** sudah bisa dirender
(sebelum diperbaiki: 31 gagal `Undefined array key "jabatan"`).

Ketika memperbaiki PF atau `profil_o`, selalu scan semua lokasi, bukan cuma satu:

```php
foreach (App\Models\Kecamatan::all() as $kec) {
    Session::put('lokasi', $kec->id);
    // build $data seperti preview(), invoke PF(), catat exception-nya
}
```

Tiga lokasi punya kondisi data khusus yang sudah ditangani:
- **362 (Cerme)** — punya logika "Direktur Utama" sendiri. Hardcoded
  `362` di view sudah diganti cukup cek `$dir->jabatan === 1` untuk semua lokasi.
- **428 (Sukodadi)** — `saham.rp_saham` & `pros_saham` tersimpan sebagai string
  berformat; view sudah membersihkannya sebelum `number_format()`.
- **234 (Wonosari)** — tidak punya user jabatan=1 level=1 sama sekali.
  Bagian tanda tangan dibiarkan kosong, bukan fatal.
