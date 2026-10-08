---
description: Jalankan test yang relevan dengan perubahan barusan saja (bukan seluruh suite).
agent: build
---

Tugasmu: menjalankan test **secara selektif** untuk perubahan yang baru saja dikerjakan di
percakapan ini. Jangan jalankan seluruh suite, jangan scan semua lokasi, jangan loop
seluruh `Kecamatan::all()`. Kalau tidak ada test yang cocok, katakan terus terang — jangan
mengarang hasil.

## Langkah 1 — Tentukan file yang berubah

```bash
git status --porcelain --untracked-files=all
git diff --name-only HEAD
```

Buat daftar `BERUBAH` dari `app/` saja (abaikan `resources/views`, `public/`, `docs/`).

Kalau `BERUBAH` kosong: berhenti, balas "tidak ada perubahan di `app/`, tidak ada test yang perlu
dijalankan." Jangan scan apa pun.

## Langkah 2 — Petakan file ke test

Pemetaan di repo ini (hanya baris ini yang dipakai, jangan menebak):

| File yang berubah | Test yang dijalankan |
|---|---|
| `app/Utils/ExcelExporter.php` | `tests/Feature/NikIdentityTest.php` |
| | `tests/Feature/OjkExportEndToEndTest.php` |
| | `tests/Feature/DrpExportTest.php` |
| `app/Support/Ojk/DrpPinjamanDiberikan.php` | `tests/Feature/DrpExportTest.php` |
| | `tests/Unit/DrpBakiDebetTest.php` |
| `app/Support/Ojk/OjkSandi.php` | `tests/Unit/OjkSandiTest.php` |
| `app/Models/SistemAngsuran.php` | `tests/Unit/SistemAngsuranRefactorTest.php` |
| `app/Utils/HitungSistemAngsuran.php` | `tests/Unit/SistemAngsuranRefactorTest.php` |

Kalau satu file dipetakan ke beberapa test, gabungkan jadi **satu** perintah phpunit:

```bash
./vendor/bin/phpunit tests/Feature/NikIdentityTest.php tests/Feature/OjkExportEndToEndTest.php
```

Hindari menjalankan file yang sama dua kali.

### Perubahan di luar `app/Utils`, `app/Support/Ojk`, `app/Models/SistemAngsuran.php`

Tidak ada test yang memverifikasi perubahan ini. Kalau hanya blade view / controller / route berubah:

- Jalankan `php -l` **hanya pada file view yang berubah**, bukan `find ... -exec` ke seluruh
  direktori:

```bash
php -l resources/views/pelaporan/view/ojk/daftar_rincian_pinjamanaktif.blade.php
```

- Lalu balas jujur: "perubahan view tidak punya test otomatis; yang dicek hanya sintaks PHP.
  Verifikasi render nyata perlu dilakukan manual." Jangan menulis "test passed".

Kalau view yang berubah dirender oleh controller yang punya test end-to-end
(mis. `OjkExportEndToEndTest`), tambahkan test itu juga secara manual dengan alasan tertulis.

## Langkah 3 — Jalankan

Jalankan per-file, **satu file per invocation**, supaya failures mudah dikaitkan ke sumbernya:

```bash
./vendor/bin/phpunit tests/Feature/NikIdentityTest.php
```

Test ini memakai database pengembangan yang sudah ada (bikin `DrpPinjamanDiberikan(109)`).
Karena itu **dilarang** menambahkan migration, seeder, factory, atau `RefreshDatabase`.
Command ini hanya memanggil test yang sudah ada.

Kalau `phpunit` tidak ada di `vendor/bin`, coba `php artisan test <file>` atau `./sail phpunit <file>`.
Jangan menjalankan `composer install` untuk ini.

## Langkah 4 — Baca hasilnya dengan benar

Baseline yang sudah diketahui **sebelum perubahan apa pun**:

```
Tests\Unit\SistemAngsuranRefactorTest::test_urutan_populated_for_used_ids   GAGAL
Tests\Unit\SistemAngsuranRefactorTest::test_urutan_unused_id_is_null        GAGAL
```

Dua kegagalan ini sudah ada sebelumnya dan bukan regresi. Kalau hanya itu yang muncul, laporkan
sebagai "baseline, bukan regresi" — jangan mengklaim harus diperbaiki.

Kegagalan lain = regresi dari perubahan barusan. Untuk setiap kegagalan:
- Sebut nama test lengkap dan file:baris
- Kutip assertion yang gagal
- Hubungkan ke file yang berubah di `BERUBAH`
- Kalau penyebabnya jelas dari pesan error, tunjukkan letaknya. Kalau tidak, katakan tidak jelas.

Jangan memperbaiki kode aplikasi di dalam command ini. Kalau memang perlu perbaikan, laporkan
dulu ke user dan tunggu persetujuan — command ini tugasnya **melaporkan**, bukan memperbaiki.

## Aturan output

- Ringkasan satu baris di awal: jumlah test dijalankan, lulus, gagal
- Daftar kegagalan (jika ada) dengan file:baris
- Status akhir salah satu dari: `LOLOS`, `ADA REGRESI`, `BASELINE SAJA`, `TIDAK ADA TEST`
- Maksimal 15 baris. Tidak perlu menempelkan output phpunit lengkap kecuali diminta.

Jangan menjalankan `view:cache`, jangan `git add`/`commit`, jangan sentuh `.env`.