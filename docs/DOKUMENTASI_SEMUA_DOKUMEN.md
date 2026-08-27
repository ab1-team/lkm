# 📋 Dokumentasi Lengkap Semua Dokumen Cetak

**Status**: Katalog Komprehensif Dokumen  
**Total Dokumen**: 50+ dokumen  
**Kategori Utama**: 6 grup (Pinjaman Individual, Pinjaman Kelompok, Transaksi, Pelaporan, Simpanan, Administrasi)  
**Terakhir Diperbarui**: 2024

---

## 📊 INDEX RINGKASAN

| NO | KATEGORI | JUMLAH | PATH | DESKRIPSI |
|----|----------|--------|------|-----------|
| 1 | **Pinjaman Individual** | 20+ | `perguliran_i/dokumen/` | SPK, Kartu Angsuran, Verifikasi, dll |
| 2 | **Pinjaman Kelompok** | 20+ | `perguliran/dokumen/` | Mirror dari individual untuk kelompok |
| 3 | **Transaksi** | 8 | `transaksi/dokumen/` | BKM, BKK, BM, Struk, Cetak Bulk |
| 4 | **Transaksi Angsuran** | 5 | `transaksi/jurnal_angsuran/dokumen/` | Struk, BKM spesifik angsuran |
| 5 | **Pelaporan** | 10+ | `pelaporan/view/` | Arus Kas, Neraca, CALK, dll |
| 6 | **Simpanan** | 3+ | `simpanan/` | Formulir, Cetak, Laporan |

**TOTAL: 50+ dokumen dalam sistem**

---

## 1️⃣ DOKUMEN PINJAMAN INDIVIDUAL (PERGULIRAN_I)

Lokasi Base: `resources\views\perguliran_i\dokumen\`  
Layout Base: `resources\views\perguliran_i\dokumen\layout\base.blade.php`

### 📄 1.1 - SURAT PERJANJIAN KREDIT (SPK)

| Atribut | Nilai |
|---------|-------|
| **File** | `spk.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/spk/{id}` |
| **Fungsi** | Perjanjian kredit legal antara lembaga & peminjam |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | 8 Pasal + Pihak Pertama & Kedua + Signature blocks |
| **Halaman** | Multi-page (2-3 halaman) |
| **Controller** | PinjamanIndividuController |

**Konten Dokumen:**
- Header: Logo, Judul "SURAT PERJANJIAN KREDIT"
- Data Pihak Pertama (Pengurus Lembaga)
- Data Pihak Kedua (Peminjam)
- 8 Pasal:
  1. Pemberian Kredit (jumlah, dasar, referensi)
  2. Penyerahan Pinjaman (metode tunai, waktu)
  3. Sistem Pengembalian (angsuran, jasa, denda)
  4. Agunan (jaminan, pegadaian, persyaratan)
  5. Pengalihan Kuasa Khusus (kuasa eksekusi)
  6. Penyelesaian Perselisihan (musyawarah → hukum)
  7. Lain-Lain (peraturan tambahan)
  8. Penutup (tanda tangan, materai, tanggal)
- Blok Tanda Tangan: Pihak Pertama, Pihak Kedua, Penjamin, Kepala Desa

**Data Context:**
```php
$pinkel          // PinjamanIndividu model
$dir             // User direktur (pihak pertama)
$kec             // Kecamatan model
$keuangan        // Keuangan utility
$ra              // RencanaAngsuran (rencana pertama)
$ttd             // Tanda tangan digital/file
```

**Utilities Digunakan:**
- `Tanggal::namaHari()`, `Tanggal::hari()`, `Tanggal::namaBulan()`, `Tanggal::tahun()`
- `$keuangan->terbilang()` - Convert angka ke teks
- `Carbon::parse()->translatedFormat()`

---

### 📄 1.2 - KARTU ANGSURAN

| Atribut | Nilai |
|---------|-------|
| **File** | `kartu_angsuran.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/kartu_angsuran/{id_pinkel}/{idtp}` |
| **Fungsi** | Catatan pembayaran angsuran individual |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Header + Tabel riwayat angsuran + Catatan |
| **Halaman** | Single atau multi-page (dinamis) |
| **Controller** | PinjamanIndividuController |

**Konten Dokumen:**
- Header: Logo, Judul "KARTU ANGSURAN"
- Data Peminjam: Nama, KTP, Alamat, Jumlah Kredit
- Tabel Angsuran dengan kolom:
  - No. Urut Angsuran
  - Tanggal Jatuh Tempo
  - Nominal Wajib Pokok
  - Nominal Wajib Jasa
  - Tanggal Pembayaran
  - Jumlah Pembayaran
  - Tunggakan (jika ada)
  - Denda (jika ada)
  - Saldo Pokok
  - Saldo Jasa
  - TTD Pembayar
- Footer: Catatan pembayaran, kondisi akhir

**Data Context:**
```php
$pinkel          // PinjamanIndividu model
$nia             // Nama Individu Angsuran (?)
$real_i_count    // Jumlah realisasi angsuran
$laporan         // Nama laporan (untuk title)
```

**CSS Style:**
- Fixed header untuk page break
- Margin bottom untuk signature space
- Font: Arial, Helvetica, sans-serif
- Size: Auto-page-break untuk list panjang

---

### 📄 1.3 - KARTU ANGSURAN ANGGOTA

| Atribut | Nilai |
|---------|-------|
| **File** | `kartu_angsuran_anggota.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/kartu_angsuran_anggota/{id}` |
| **Fungsi** | Cetak kartu untuk individual anggota pinjaman |
| **Format Output** | HTML / PDF |
| **Komponen** | Similar to kartu_angsuran |
| **Halaman** | Single/Multi-page |

**Catatan:** Mirip kartu_angsuran tetapi khusus untuk individual member

---

### 📄 1.4 - CEK LIST (CHECKLIST PROPOSAL)

| Atribut | Nilai |
|---------|-------|
| **File** | `cek_list.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/cek_list/{id}` |
| **Fungsi** | Verifikasi kelengkapan dokumen proposal |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Tabel checklist dengan kolom: No, Nama Dokumen, Cukup, Kurang, Tidak Ada, Catatan |
| **Halaman** | 1-2 halaman |
| **Controller** | PinjamanIndividuController |

**Konten Dokumen:**
- Judul: "CHECK LIST KELENGKAPAN PROPOSAL {JPP}"
- Header Info Proposal
- Tabel Checklist dengan items:
  1. Formulir Pinjaman
  2. Formulir Verifikasi
  3. KTP Peminjam
  4. KTP Suami/Istri (jika ada)
  5. Kartu Keluarga
  6. Bukti Kediaman
  7. Jaminan/Agunan (dokumen)
  8. Rencana Usaha/Bisnis
  9. Pernyataan Bebas dari Hutang
  10. Referensi Karakter
  ... (dan lebih banyak)

**Data Context:**
```php
$pinkel          // PinjamanIndividu model
$pinkel->jpp     // Jenis Produk Pinjaman
```

**Style:**
- Tabel dengan border lengkap
- Background header abu-abu
- Font size 10pt
- Table-layout: fixed untuk kontrol kolom

---

### 📄 1.5 - FORM VERIFIKASI ANGGOTA

| Atribut | Nilai |
|---------|-------|
| **File** | `form_verifikasi_anggota.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/form_verifikasi_anggota/{id}` |
| **Fungsi** | Form verifikasi kondisi usaha/tempat tinggal |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Formulir dengan pertanyaan verifikasi |
| **Halaman** | 1-2 halaman |

**Konten:**
- Data Anggota
- Checklist Verifikasi:
  - Kondisi Rumah
  - Status Usaha
  - Penghasilan
  - Karakter
  - Kemampuan Bayar
  - TTD Verifikator

---

### 📄 1.6 - FORM VERIFIKASI (UMUM)

| Atribut | Nilai |
|---------|-------|
| **File** | `form_verifikasi.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/form_verifikasi/{id}` |
| **Fungsi** | Form verifikasi umum (untuk group/individual) |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Formulir verifikasi detail |
| **Halaman** | Multi-page |

---

### 📄 1.7 - DAFTAR HADIR VERIFIKASI

| Atribut | Nilai |
|---------|-------|
| **File** | `daftar_hadir_verifikasi.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/daftar_hadir_verifikasi/{id}` |
| **Fungsi** | Absensi peserta verifikasi |
| **Format Output** | HTML / PDF (A4 Landscape) |
| **Komponen** | Tabel: No, Nama, Tanda Tangan |
| **Halaman** | 1-2 halaman |

**Konten:**
- Header: Tanggal, Lokasi, Tim Verifikasi
- Tabel Peserta:
  - No. Urut
  - Nama Peserta
  - NIK
  - Tanda Tangan
- Footer: Dokumentasi, Catatan

---

### 📄 1.8 - DAFTAR HADIR PENCAIRAN

| Atribut | Nilai |
|---------|-------|
| **File** | `daftar_hadir_pencairan.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/daftar_hadir_pencairan/{id}` |
| **Fungsi** | Absensi penerima saat pencairan dana |
| **Format Output** | HTML / PDF (A4 Landscape) |
| **Komponen** | Tabel peserta + Jumlah yang diterima |
| **Halaman** | 1-2 halaman |

---

### 📄 1.9 - BERITA ACARA PENCAIRAN

| Atribut | Nilai |
|---------|-------|
| **File** | `ba_pencairan.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/ba_pencairan/{id}` |
| **Fungsi** | Dokumen formal pencairan dana |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | BA resmi dengan item-item |
| **Halaman** | 1-3 halaman |
| **Signature** | Bendahara, Ketua, Saksi |

**Konten:**
- Header: Judul "BERITA ACARA PENCAIRAN DANA"
- Tanggal & Lokasi
- Pihak yang Terlibat
- Rincian Dana Dicairkan:
  - Jumlah Penerima
  - Total Dana
  - Perincian per item (pokok, jasa, biaya)
- Pernyataan telah diterima
- Catatan
- Tanda Tangan (Bendahara, Ketua, Saksi)

---

### 📄 1.10 - BERITA ACARA MUSYAWARAH

| Atribut | Nilai |
|---------|-------|
| **File** | `ba_musyawarah.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/ba_musyawarah/{id}` |
| **Fungsi** | Dokumen hasil musyawarah kelompok |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | BA musyawarah dengan keputusan |
| **Halaman** | 1-2 halaman |

**Konten:**
- Tanggal & Lokasi Musyawarah
- Peserta (list anggota)
- Agenda Musyawarah
- Hasil Diskusi
- Keputusan Bersama
- Rencana Tindak Lanjut
- TTD Peserta & Pemandu

---

### 📄 1.11 - BERITA ACARA PENDANAAN

| Atribut | Nilai |
|---------|-------|
| **File** | `ba_pendanaan.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/ba_pendanaan/{id}` |
| **Fungsi** | BA proses pendanaan/operasional |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | BA pendanaan formal |
| **Halaman** | 1-2 halaman |

---

### 📄 1.12 - COVER PROPOSAL

| Atribut | Nilai |
|---------|-------|
| **File** | `cover_proposal.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/cover_proposal/{id}` |
| **Fungsi** | Halaman sampul proposal pinjaman |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Logo, Judul, Data Peminjam, Tanggal |
| **Halaman** | 1 halaman |

**Konten:**
- Logo Lembaga (centered top)
- Judul: "PROPOSAL PERMOHONAN PINJAMAN"
- JPP (Jenis Produk Pinjaman)
- Data Peminjam:
  - Nama
  - NIK/No. KTP
  - Alamat
  - Usaha/Bidang
  - Jumlah Proposal
- Tanggal Proposal
- TTD Kepala (bottom)

---

### 📄 1.13 - COVER PENCAIRAN

| Atribut | Nilai |
|---------|-------|
| **File** | `cover_pencairan.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/cover_pencairan/{id}` |
| **Fungsi** | Halaman sampul dokumen pencairan |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Logo, Judul Pencairan, Info Dasar |
| **Halaman** | 1 halaman |

---

### 📄 1.14 - KTP (IDENTITAS PRIBADI)

| Atribut | Nilai |
|---------|-------|
| **File** | `ktp.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/ktp/{id}` |
| **Fungsi** | Scan/Cetak data KTP peminjam |
| **Format Output** | HTML / PDF (A4 Landscape) |
| **Komponen** | Tabel data KTP (Photo ready print) |
| **Halaman** | 1 halaman |

**Konten:**
- Foto Peminjam (jika ada)
- Data KTP:
  - No. KTP
  - Nama Lengkap
  - Tempat/Tanggal Lahir
  - Jenis Kelamin
  - Alamat
  - Agama
  - Pekerjaan
  - Status Perkawinan
- Tanda Tangan Peminjam

---

### 📄 1.15 - ANGGOTA (DATA ANGGOTA)

| Atribut | Nilai |
|---------|-------|
| **File** | `anggota.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/anggota/{id}` |
| **Fungsi** | Cetak data anggota kelompok pinjaman |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | List anggota dengan detail |
| **Halaman** | 1-2 halaman |

**Konten:**
- Tabel Anggota:
  - No. Urut
  - Nama Lengkap
  - KTP
  - Alamat
  - Usaha
  - Jumlah Pinjaman
  - TTD

---

### 📄 1.16 - KUITANSI (TANDA TERIMA)

| Atribut | Nilai |
|---------|-------|
| **File** | `kuitansi.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/kuitansi/{id}` |
| **Fungsi** | Tanda terima dana cair |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Kuitansi formal dengan nomor seri |
| **Halaman** | 1 halaman (2 rangkap) |

**Konten:**
- Nomor Kuitansi
- Tanggal Penerimaan
- Penerima: Nama, KTP, Alamat
- Penyerah: Nama Lembaga
- Jumlah: Nominal + Terbilang
- Keterangan: "Telah Diterima untuk Kredit..."
- TTD Penerima
- TTD Penyerah
- Logo & cap lembaga

---

### 📄 1.17 - KUITANSI ANGGOTA (Variant)

| Atribut | Nilai |
|---------|-------|
| **File** | `kuitansi_anggota.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/kuitansi_anggota/{id}` |
| **Fungsi** | Kuitansi untuk individual anggota |
| **Format Output** | HTML / PDF |
| **Komponen** | Kuitansi dengan identitas anggota |
| **Halaman** | 1 halaman |

---

### 📄 1.18 - ANALISIS KEPUTUSAN KREDIT

| Atribut | Nilai |
|---------|-------|
| **File** | `analisis_keputusan_kredit.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/analisis_keputusan_kredit/{id}` |
| **Fungsi** | Analisis dan rekomendasi keputusan kredit |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Formulir analisis 5C (Character, Capacity, Capital, Collateral, Condition) |
| **Halaman** | 2-3 halaman |

**Konten:**
- Data Calon Peminjam
- Analisis 5C:
  1. **Character** (Karakter/Reputasi)
  2. **Capacity** (Kemampuan membayar)
  3. **Capital** (Modal/Aset)
  4. **Collateral** (Jaminan/Agunan)
  5. **Condition** (Kondisi Ekonomi)
- Kesimpulan Analisis
- Rekomendasi (Approve/Reject/Conditional)
- TTD Analis & Approval

---

### 📄 1.19 - AGUNGAN JAMINAN

| Atribut | Nilai |
|---------|-------|
| **File** | `agungan_jaminan.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/agungan_jaminan/{id}` |
| **Fungsi** | Dokumentasi jaminan/agunan kredit |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Tabel detail agunan |
| **Halaman** | 1-2 halaman |

**Konten:**
- Data Peminjam
- Tabel Agunan:
  - Jenis Barang
  - Spesifikasi
  - Kondisi
  - Nilai Perkiraan
  - Dokumen Kepemilikan
  - TTD Peminjam
  - TTD Penilai
- Catatan Khusus Agunan

---

### 📄 1.20 - IPTW (INSERT TITLE)

| Atribut | Nilai |
|---------|-------|
| **File** | `iptw.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/iptw/{id}` |
| **Fungsi** | (Needs investigation - IPTW acronym) |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | TBD |
| **Halaman** | TBD |

---

### 📄 1.21 - CHECK (CHECKLIST - Variant)

| Atribut | Nilai |
|---------|-------|
| **File** | `check.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/check/{id}` |
| **Fungsi** | Checklist verifikasi (variant) |
| **Format Output** | HTML / PDF |
| **Komponen** | Checklist items |
| **Halaman** | 1-2 halaman |

---

### 📄 1.22 - SPK VARIANT (SPK_15)

| Atribut | Nilai |
|---------|-------|
| **File** | `spk_15.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/spk_15/{id}` |
| **Fungsi** | SPK variant (specific to location 15?) |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Similar to spk.blade.php |
| **Halaman** | Multi-page |

---

### 📄 1.23 - CETAK KARTU ANGSURAN

| Atribut | Nilai |
|---------|-------|
| **File** | `cetak_kartu_angsuran.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/cetak_kartu_angsuran/{id}` |
| **Fungsi** | Print kartu angsuran dengan format tertentu |
| **Format Output** | HTML / PDF |
| **Komponen** | Kartu dengan riwayat angsuran |
| **Halaman** | Multi-page |

---

### 📄 1.24 - CETAK KARTU ANGSURAN ANGGOTA

| Atribut | Nilai |
|---------|-------|
| **File** | `cetak_kartu_angsuran_anggota.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/cetak_kartu_angsuran_anggota/{id}` |
| **Fungsi** | Print kartu angsuran individual anggota |
| **Format Output** | HTML / PDF |
| **Komponen** | Kartu individual dengan history |
| **Halaman** | Multi-page |

---

### 📄 1.25 - CATATAN BIMBINGAN

| Atribut | Nilai |
|---------|-------|
| **File** | `catatan_bimbingan.blade.php` |
| **Rute Trigger** | `/perguliran_i/dokumen/catatan_bimbingan/{id}` |
| **Fungsi** | Pencatatan bimbingan usaha/pemantauan |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Formulir bimbingan dengan catatan |
| **Halaman** | 1-2 halaman |

**Konten:**
- Data Peminjam
- Tanggal Bimbingan
- Pembimbing
- Topik Bimbingan
- Catatan Hasil Bimbingan
- Kendala yang Dihadapi
- Solusi/Rekomendasi
- TTD Pembimbing & Peminjam

---

## 2️⃣ DOKUMEN PINJAMAN KELOMPOK (PERGULIRAN)

Lokasi Base: `resources\views\perguliran\dokumen\`  
Layout Base: `resources\views\perguliran\dokumen\layout\base.blade.php`

**Catatan Penting:** Dokumen perguliran (kelompok) adalah mirror/duplikat dari perguliran_i (individual) dengan penyesuaian data untuk kelompok.

**Jumlah Dokumen:** 20+ file dengan pola penamaan yang sama:

```
perguliran/dokumen/
├── spk.blade.php                          // SPK kelompok
├── kartu_angsuran.blade.php               // Kartu angsuran kelompok
├── kartu_angsuran_anggota.blade.php       // Kartu anggota dalam kelompok
├── cek_list.blade.php                     // Checklist kelompok
├── form_verifikasi.blade.php              // Form verifikasi kelompok
├── form_verifikasi_anggota.blade.php      // Form verifikasi per anggota
├── daftar_hadir_verifikasi.blade.php      // Absensi verifikasi
├── daftar_hadir_pencairan.blade.php       // Absensi pencairan
├── ba_pencairan.blade.php                 // BA pencairan kelompok
├── ba_musyawarah.blade.php                // BA musyawarah kelompok
├── ba_pendanaan.blade.php                 // BA pendanaan
├── cover_proposal.blade.php               // Cover proposal
├── cover_pencairan.blade.php              // Cover pencairan
├── ktp.blade.php                          // Data KTP ketua/anggota
├── anggota.blade.php                      // List anggota kelompok
├── kuitansi.blade.php                     // Kuitansi kelompok
├── kuitansi_anggota.blade.php             // Kuitansi per anggota
├── analisis_keputusan_kredit.blade.php    // Analisis kelompok
├── agungan_jaminan.blade.php              // Jaminan kolektif
├── iptw.blade.php                         // IPTW kelompok
├── check.blade.php                        // Checklist variant
├── surat_kelayakan.blade.php              // Surat kelayakan kelompok
└── cetak_kartu_angsuran*.blade.php        // Cetak kartu variants
```

**Perbedaan Utama:**
- Data `$pinkel` → referensi ke `PinjamanKelompok` model
- Data anggota → collection anggota dalam kelompok
- Tanda tangan: Ketua Kelompok, bukan individual
- Jumlah penerima: Multiple (semua anggota)
- Rute: `/perguliran/dokumen/...` instead of `/perguliran_i/dokumen/...`

---

### 📄 2.1 - SURAT KELAYAKAN KELOMPOK

| Atribut | Nilai |
|---------|-------|
| **File** | `surat_kelayakan.blade.php` |
| **Rute Trigger** | `/perguliran/dokumen/surat_kelayakan/{id}` |
| **Fungsi** | Surat rekomendasi kelayakan kredit kelompok |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Surat resmi dengan rekomendasi |
| **Halaman** | 1-2 halaman |
| **Controller** | PinjamanController |

**Konten:**
- Header Surat (Nomor, Tanggal, Sifat, Perihal)
- Tujuan: (Nama Bank/Lembaga)
- Isi:
  - Pengantar
  - Data Kelompok & Anggota
  - Hasil Analisis Kelayakan
  - Rincian Pinjaman Direkomendasikan
  - Syarat & Ketentuan
- Kesimpulan: Approve/Reject/Conditional
- TTD: Kepala Divisi, Tanggal
- Cap Lembaga

**Data Context:**
```php
$pinkel          // PinjamanKelompok model
$tgl_dana        // Tanggal dana (perolehan/referensi)
$kab             // Kabupaten model
```

---

## 3️⃣ DOKUMEN TRANSAKSI

Lokasi Base: `resources\views\transaksi\dokumen\`

### 📄 3.1 - BUKTI KAS MASUK (BKM)

| Atribut | Nilai |
|---------|-------|
| **File** | `bkm.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/bkm/{id}` |
| **Fungsi** | Bukti kas masuk (revenue entry) |
| **Format Output** | HTML / PDF (14cm x 9cm box) |
| **Komponen** | Box dokumen + Detail transaksi |
| **Halaman** | 1 halaman (2x2 layout) |
| **Controller** | TransaksiController |

**Konten:**
- Box 1-4 (printable side-by-side):
  - Header: "BUKTI KAS MASUK"
  - Nomor BKM
  - Tanggal
  - Penerima/Dari
  - Jumlah: Nominal + Terbilang
  - Keterangan/Uraian
  - TTD Penerima
  - TTD Kasir

**Style CSS:**
- Box size: 14cm x 9cm (fixed print size)
- Border: 2px solid black
- Font: 9px Arial
- Padding: 16px left, 22px right, 12px bottom, 16px top

**Data Context:**
```php
$trx             // Transaksi model
$no_bkm          // Nomor urut BKM
$tgl             // Tanggal transaksi
```

---

### 📄 3.2 - BUKTI KAS KELUAR (BKK)

| Atribut | Nilai |
|---------|-------|
| **File** | `bkk.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/bkk/{id}` |
| **Fungsi** | Bukti kas keluar (expense/payment) |
| **Format Output** | HTML / PDF (14cm x 9cm box) |
| **Komponen** | Box dokumen kas keluar |
| **Halaman** | 1 halaman (2x2 layout) |

**Konten:** Similar BKM tapi:
- Judul: "BUKTI KAS KELUAR"
- Pihak: Dibayarkan Kepada (bukan dari)
- Uraian: Keterangan pengeluaran

---

### 📄 3.3 - BUKTI MUTASI (BM)

| Atribut | Nilai |
|---------|-------|
| **File** | `bm.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/bm/{id}` |
| **Fungsi** | Bukti mutasi antar rekening |
| **Format Output** | HTML / PDF (14cm x 9cm box) |
| **Komponen** | Box dokumen mutasi |
| **Halaman** | 1 halaman |

**Konten:**
- Judul: "BUKTI MUTASI REKENING"
- Dari Rekening (asal)
- Ke Rekening (tujuan)
- Jumlah
- Keterangan
- TTD Kasir & Verifikator

---

### 📄 3.4 - KUITANSI TRANSAKSI

| Atribut | Nilai |
|---------|-------|
| **File** | `kuitansi.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/kuitansi/{id}` |
| **Fungsi** | Kuitansi formal transaksi |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Kuitansi standar |
| **Halaman** | 1 halaman (2 rangkap) |

**Konten:**
- Nomor Kuitansi
- Tanggal
- Penerima/Pembayar
- Terbilang Jumlah
- Untuk: Keterangan (angsuran, setoran, dll)
- TTD & Materai

---

### 📄 3.5 - CETAK DOKUMEN BULK

| Atribut | Nilai |
|---------|-------|
| **File** | `cetak.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/cetak` (POST) |
| **Fungsi** | Cetak multiple dokumen transaksi sekaligus |
| **Format Output** | PDF (A4 Landscape) |
| **Komponen** | Multiple BKM/BKK/BM dalam 1 PDF |
| **Halaman** | Multi-page |
| **Controller** | TransaksiController::cetak() |

**Konten:**
- List transaksi yang di-check
- Render individual dokumen untuk setiap transaksi
- Page break antar dokumen
- Summary total

**Processing:**
```php
// controller
$view = view('transaksi.dokumen.cetak', $data)->render();
$pdf = PDF::loadHTML($view)->setPaper('A4', 'landscape');
return $pdf->stream();
```

---

## 4️⃣ DOKUMEN TRANSAKSI ANGSURAN

Lokasi Base: `resources\views\transaksi\jurnal_angsuran\dokumen\`

### 📄 4.1 - STRUK ANGSURAN (THERMAL)

| Atribut | Nilai |
|---------|-------|
| **File** | `struk.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/struk/{id}` |
| **Fungsi** | Struk pembayaran angsuran format thermal printer |
| **Format Output** | HTML / PDF (58mm width) |
| **Komponen** | Detail angsuran + Riwayat |
| **Halaman** | 1-2 halaman |
| **Controller** | TransaksiController::struk() |

**Konten:**
- Header: Logo, "STRUK PEMBAYARAN ANGSURAN"
- Data Transaksi:
  - No. Referensi
  - Tanggal Transaksi
  - Jam
- Data Peminjam:
  - Nama
  - No. Pinjaman
  - Jumlah Pinjaman
- Detail Angsuran:
  - Angsuran ke-X dari Y
  - Tanggal Jatuh Tempo
  - Tunggakan Sebelumnya
  - Pembayaran Pokok
  - Pembayaran Jasa
  - Denda (jika ada)
  - Total Bayar
  - Saldo Pokok Sisa
  - Saldo Jasa Sisa
- Catatan Bulan Depan (projeksi)
- TTD Kasir & Peminjam
- Timestamp Print

**Data Context:**
```php
$real            // RealAngsuran record
$ra              // RencanaAngsuran (latest)
$ra_bulan_ini    // RencanaAngsuran current month
$pinkel          // PinjamanIndividu/Kelompok
$user            // User kasir
$kec             // Kecamatan
$keuangan        // Utilities
```

**Utilities:**
- `Tanggal::tglIndo()` - Tanggal Indonesia
- `number_format()` - Format nominal
- `$keuangan->terbilang()` - Teks nominal

**CSS Style:**
- Thermal printer width: 58mm = ~220px
- Font: 8-9px
- No margin (compact)
- Monospace font for alignment

---

### 📄 4.2 - STRUK DOT MATRIX

| Atribut | Nilai |
|---------|-------|
| **File** | `struk_thermal.blade.php` (meskipun nama thermal, fungsi matrix) |
| **Rute Trigger** | `/transaksi/dokumen/struk_matrix/{id}` |
| **Fungsi** | Struk pembayaran angsuran format dot matrix printer |
| **Format Output** | HTML / PDF (80-column dot matrix) |
| **Komponen** | Detail angsuran format matrix |
| **Halaman** | 1-2 halaman |
| **Controller** | TransaksiController::strukMatrix() |

**Konten:** Similar struk.blade.php dengan:
- Width: 80 column standard
- Font: Monospace (Courier)
- Line char boundaries untuk alignment
- Fixed column positions

---

### 📄 4.3 - BKM ANGSURAN

| Atribut | Nilai |
|---------|-------|
| **File** | `bkm.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/bkm_angsuran/{id}` |
| **Fungsi** | Bukti Kas Masuk khusus pembayaran angsuran |
| **Format Output** | HTML / PDF (A4 atau box format) |
| **Komponen** | BKM dengan detail angsuran |
| **Halaman** | 1 halaman |
| **Controller** | TransaksiController (likely) |

**Perbedaan dari BKM reguler:**
- Reference: No. Pinjaman + Angsuran ke
- Keterangan: "Pembayaran Angsuran [JPP]"
- Detail: Pokok vs Jasa breakdown

---

## 5️⃣ DOKUMEN TRANSAKSI ANGSURAN INDIVIDU

Lokasi: `resources\views\transaksi\jurnal_angsuran\individu\dokumen\`

### 📄 5.1 - STRUK INDIVIDU

| Atribut | Nilai |
|---------|-------|
| **File** | `struk.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/struk_individu/{id}` |
| **Fungsi** | Struk untuk individual anggota pinjaman |
| **Format Output** | HTML / PDF (58mm thermal) |
| **Komponen** | Struk dengan identitas anggota |
| **Halaman** | 1-2 halaman |

---

### 📄 5.2 - STRUK MATRIX INDIVIDU

| Atribut | Nilai |
|---------|-------|
| **File** | `struk_matrix.blade.php` |
| **Rute Trigger** | `/transaksi/dokumen/struk_matrix_individu/{id}` |
| **Fungsi** | Struk matrix format untuk individual |
| **Format Output** | HTML / PDF (80-column) |
| **Komponen** | Struk matrix individual |
| **Halaman** | 1-2 halaman |

---

## 6️⃣ DOKUMEN PELAPORAN

Lokasi Base: `resources\views\pelaporan\view\`

**Catatan:** Dokumen pelaporan lebih kompleks dengan multi-month, multi-year support

### 📄 6.1 - LAPORAN ARUS KAS

| Atribut | Nilai |
|---------|-------|
| **File** | `arus_kas.blade.php` |
| **Rute Trigger** | `/pelaporan/arus_kas?tahun=2024&bulan=1` |
| **Fungsi** | Laporan arus kas (cash flow) bulanan |
| **Format Output** | HTML / PDF (A4 Landscape) |
| **Komponen** | Tabel arus kas dengan kolom: Awal, Masuk, Keluar, Akhir |
| **Halaman** | 1-2 halaman |

**Konten:**
- Header: Logo, Periode, Nama Lembaga
- Tabel Arus Kas:
  - Saldo Awal (per akun)
  - Kas Masuk (detail item per hari)
  - Kas Keluar (detail item per hari)
  - Saldo Akhir (per akun)
- Total baris
- TTD Kepala & Kasir
- Tanggal cetak

---

### 📄 6.2 - NERACA SALDO

| Atribut | Nilai |
|---------|-------|
| **File** | `neraca_saldo.blade.php` |
| **Rute Trigger** | `/pelaporan/neraca_saldo?tahun=2024&bulan=1` |
| **Fungsi** | Trial balance (neraca saldo) |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Tabel debit-kredit per akun |
| **Halaman** | 1-2 halaman |

**Konten:**
- Tabel:
  - No. Akun
  - Nama Akun
  - Saldo Debit
  - Saldo Kredit
- Total Debit = Total Kredit validation
- Catatan: Balanced/Not Balanced

---

### 📄 6.3 - CALK (CATATAN ATAS LAPORAN KEUANGAN)

| Atribut | Nilai |
|---------|-------|
| **File** | `calk.blade.php` |
| **Rute Trigger** | `/pelaporan/calk?tahun=2024` |
| **Fungsi** | Catatan penjelasan atas laporan keuangan |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Daftar catatan dengan penjelasan detail |
| **Halaman** | Multi-page |

**Konten:**
- Pengantar
- 1. Identitas Lembaga
- 2. Ikhtisar Kebijakan Akuntansi
- 3. Penjelasan Pos-Pos Neraca
- 4. Penjelasan Pos-Pos Laba-Rugi
- 5. Penjelasan Pinjaman & Simpanan
- 6. Penjelasan Aset Tetap
- 7. Penjelasan Contingent Liabilities
- 8. Event Setelah Neraca

---

### 📄 6.4 - REKAP CALK

| Atribut | Nilai |
|---------|-------|
| **File** | `rekap_calk.blade.php` |
| **Rute Trigger** | `/pelaporan/rekap_calk?tahun=2024` |
| **Fungsi** | Ringkasan CALK untuk executive summary |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Summary bullets dari CALK |
| **Halaman** | 1-2 halaman |

---

### 📄 6.5 - LAPORAN LABA-RUGI

| Atribut | Nilai |
|---------|-------|
| **File** | `laporan_laba_rugi.blade.php` (implied, dalam TransaksiController::lpp()) |
| **Rute Trigger** | `/transaksi/dokumen/lpp/{id}` |
| **Fungsi** | Laporan laba-rugi periode |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Income statement dengan revenue - expense = profit |
| **Halaman** | 1-2 halaman |

**Konten:**
- Revenue Items:
  - Pendapatan Jasa Pinjaman
  - Pendapatan Simpanan
  - Pendapatan Administrasi
  - Pendapatan Lain
- Total Revenue
- Expense Items:
  - Biaya Operasional
  - Biaya Pegawai
  - Biaya Penyusutan
  - Biaya Lain
- Total Expense
- **NET PROFIT/LOSS**

---

### 📄 6.6 - JURNAL UMUM REPORT

| Atribut | Nilai |
|---------|-------|
| **File** | `jurnal_umum.blade.php` (implied dalam pelaporan) |
| **Rute Trigger** | `/pelaporan/jurnal_umum?tahun=2024&bulan=1` |
| **Fungsi** | Laporan jurnal umum (general ledger) |
| **Format Output** | HTML / PDF (A4 Landscape) |
| **Komponen** | Tabel semua transaksi per akun |
| **Halaman** | Multi-page |

**Konten:**
- Tabel per Akun:
  - Tanggal
  - No. Voucher/Referensi
  - Uraian
  - Debit
  - Kredit
  - Saldo
- Subtotal per Akun
- Grand Total

---

### 📄 6.7 - COVER LAPORAN

| Atribut | Nilai |
|---------|-------|
| **File** | `cover.blade.php` |
| **Rute Trigger** | `/pelaporan/cover?tahun=2024` |
| **Fungsi** | Sampul laporan keuangan |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Cover page formal |
| **Halaman** | 1 halaman |

**Konten:**
- Logo Lembaga (centered)
- Judul: "LAPORAN KEUANGAN"
- Periode: "Tahun 2024"
- Nama Lembaga
- Lokasi
- Blank space untuk signatures
- TTD: Kepala, Kasir, Auditor (jika ada)

---

### 📄 6.8 - PERKEMBANGAN PIUTANG - KOLEK INDIVIDU

| Atribut | Nilai |
|---------|-------|
| **File** | `perkembangan_piutang\kolek_individu.blade.php` |
| **Rute Trigger** | `/pelaporan/perkembangan_piutang/kolek_individu?tahun=2024` |
| **Fungsi** | Laporan perkembangan piutang/kolektibilitas individu |
| **Format Output** | HTML / PDF (A4 Landscape) |
| **Komponen** | Tabel perkembangan piutang per bulan |
| **Halaman** | 1-2 halaman |

**Konten:**
- Tabel Progress Piutang:
  - Bulan
  - Jumlah Peminjam
  - Outstanding Principal
  - Outstanding Interest
  - % Collection Rate
  - NPL (Non Performing Loan) %
- Tren visual (optional)
- Analisis singkat

---

## 7️⃣ DOKUMEN SIMPANAN

Lokasi: `resources\views\simpanan\`

### 📄 7.1 - CETAK FORMULIR SIMPANAN

| Atribut | Nilai |
|---------|-------|
| **File** | `partials\cetak_formulir.blade.php` |
| **Rute Trigger** | `/simpanan/cetak_formulir/{id}` |
| **Fungsi** | Print statement simpanan member |
| **Format Output** | HTML / PDF (A4 Portrait) |
| **Komponen** | Struk dengan auto-trigger print |
| **Halaman** | 1-2 halaman |

**Konten:**
- Data Saver:
  - Nama
  - No. Rekening Simpanan
  - Tanggal Bergabung
- Tabel Transaksi Simpanan:
  - Tanggal
  - Jenis (Setor/Tarik)
  - Nominal
  - Saldo
- Saldo Akhir
- Bunga (jika ada)
- Footer: User yang cetak, timestamp

**Special Feature:**
```javascript
window.onload = function() {
    window.print();  // Auto-trigger print
}
```

---

### 📄 7.2 - LAPORAN SIMPANAN

| Atribut | Nilai |
|---------|-------|
| **File** | (implied) |
| **Rute Trigger** | `/simpanan/laporan/{id}` |
| **Fungsi** | Laporan detil simpanan per member |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Statement lengkap |
| **Halaman** | 1-2 halaman |

---

## 8️⃣ DOKUMEN SOP & ADMINISTRASI

Lokasi: `resources\views\sop\`, `resources\views\layouts\`

### 📄 8.1 - SOP DOCUMENTS

| Atribut | Nilai |
|---------|-------|
| **File** | `sop\index.blade.php` dan files terkait |
| **Rute Trigger** | `/sop` (main), `/sop/view/{sop_id}` (detail) |
| **Fungsi** | Standar Operasional Prosedur lembaga |
| **Format Output** | HTML / PDF (A4) |
| **Komponen** | Daftar SOP + Detail SOP |
| **Halaman** | Multi-page per SOP |

**Konten Daftar:**
- Kategori SOP:
  - Umum
  - Pinjaman
  - Simpanan
  - Administrasi
  - Teknologi
- Per kategori: List SOP dengan tanggal efektif

**Konten Detail SOP:**
- Judul SOP
- Tujuan
- Ruang Lingkup
- Tanggung Jawab (siapa, posisi)
- Prosedur Step-by-step:
  1. Input/Persiapan
  2. Proses (steps)
  3. Output/Dokumentasi
  4. Quality Check
- Formulir/Template terkait
- Lampiran dokumen
- TTD Kepala & Tanggal efektif

---

### 📄 8.2 - USERS MANAGEMENT SOP

| Atribut | Nilai |
|---------|-------|
| **File** | `sop\users.blade.php` |
| **Rute Trigger** | `/sop/users` |
| **Fungsi** | SOP manajemen user & akses |
| **Format Output** | HTML / PDF |
| **Komponen** | Tabel user, role, permission |
| **Halaman** | 1-2 halaman |

---

## 9️⃣ LAYOUT TEMPLATES

### 📄 9.1 - BASE LAYOUT PERGULIRAN_I

| Atribut | Nilai |
|---------|-------|
| **File** | `perguliran_i\dokumen\layout\base.blade.php` |
| **Fungsi** | Template utama untuk semua dokumen perguliran_i |
| **Digunakan Oleh** | 25+ dokumen @extends |
| **Komponen** | HTML head, CSS print styles, header/footer, @yield |

**Struktur:**
```php
@extends('perguliran_i.dokumen.layout.base')
@section('content')
    <!-- Dokumen konten -->
@endsection
```

---

### 📄 9.2 - BASE LAYOUT PERGULIRAN

| Atribut | Nilai |
|---------|-------|
| **File** | `perguliran\dokumen\layout\base.blade.php` |
| **Fungsi** | Template utama untuk dokumen perguliran (kelompok) |
| **Digunakan Oleh** | 25+ dokumen |
| **Komponen** | Similar struktur base.blade.php |

---

## 🔟 MATRIX RUTE DOKUMEN

### Complete Mapping

| Kategori | File | Rute | Format | View |
|----------|------|------|--------|------|
| **PINJAMAN INDIVIDUAL** | | | | |
| SPK | spk.blade.php | `/perguliran_i/dokumen/spk/{id}` | PDF/HTML | Multi-page |
| Kartu Angsuran | kartu_angsuran.blade.php | `/perguliran_i/dokumen/kartu_angsuran/{id}` | PDF/HTML | Multi-page |
| Kartu Angsuran Anggota | kartu_angsuran_anggota.blade.php | `/perguliran_i/dokumen/kartu_angsuran_anggota/{id}` | PDF/HTML | Multi-page |
| Cek List | cek_list.blade.php | `/perguliran_i/dokumen/cek_list/{id}` | PDF/HTML | 1-2 page |
| Form Verifikasi | form_verifikasi.blade.php | `/perguliran_i/dokumen/form_verifikasi/{id}` | PDF/HTML | 1-2 page |
| Form Verifikasi Anggota | form_verifikasi_anggota.blade.php | `/perguliran_i/dokumen/form_verifikasi_anggota/{id}` | PDF/HTML | 1-2 page |
| **PINJAMAN KELOMPOK** | | | | |
| SPK | spk.blade.php | `/perguliran/dokumen/spk/{id}` | PDF/HTML | Multi-page |
| Surat Kelayakan | surat_kelayakan.blade.php | `/perguliran/dokumen/surat_kelayakan/{id}` | PDF/HTML | 1-2 page |
| (20+ more like above) | ... | `/perguliran/dokumen/...` | ... | ... |
| **TRANSAKSI** | | | | |
| BKM | bkm.blade.php | `/transaksi/dokumen/bkm/{id}` | HTML/PDF | 1 page (2x2) |
| BKK | bkk.blade.php | `/transaksi/dokumen/bkk/{id}` | HTML/PDF | 1 page (2x2) |
| BM | bm.blade.php | `/transaksi/dokumen/bm/{id}` | HTML/PDF | 1 page (2x2) |
| Kuitansi | kuitansi.blade.php | `/transaksi/dokumen/kuitansi/{id}` | PDF/HTML | 1 page |
| Cetak Bulk | cetak.blade.php | `/transaksi/dokumen/cetak` (POST) | PDF | Multi-page |
| **TRANSAKSI ANGSURAN** | | | | |
| Struk Thermal | struk.blade.php | `/transaksi/dokumen/struk/{id}` | HTML/PDF | 1-2 page (58mm) |
| Struk Matrix | struk_thermal.blade.php | `/transaksi/dokumen/struk_matrix/{id}` | HTML/PDF | 1-2 page (80col) |
| BKM Angsuran | bkm.blade.php | `/transaksi/dokumen/bkm_angsuran/{id}` | HTML/PDF | 1 page |
| **PELAPORAN** | | | | |
| Arus Kas | arus_kas.blade.php | `/pelaporan/arus_kas?tahun=X&bulan=Y` | HTML/PDF | 1-2 page |
| Neraca Saldo | neraca_saldo.blade.php | `/pelaporan/neraca_saldo?tahun=X&bulan=Y` | HTML/PDF | 1-2 page |
| CALK | calk.blade.php | `/pelaporan/calk?tahun=X` | HTML/PDF | Multi-page |
| Rekap CALK | rekap_calk.blade.php | `/pelaporan/rekap_calk?tahun=X` | HTML/PDF | 1-2 page |
| Laporan Laba Rugi | (implied) | `/transaksi/dokumen/lpp/{id}` | HTML/PDF | 1-2 page |
| Cover Laporan | cover.blade.php | `/pelaporan/cover?tahun=X` | HTML/PDF | 1 page |
| **SIMPANAN** | | | | |
| Cetak Formulir | cetak_formulir.blade.php | `/simpanan/cetak_formulir/{id}` | HTML (auto-print) | 1-2 page |

---

## 1️⃣1️⃣ DATA CONTEXT UNIVERSAL

Semua dokumen menggunakan beberapa data umum:

```php
// Authentication & Location
auth()->user()              // Current user (kasir/admin/kepala)
Session::get('lokasi')      // Current location/branch ID

// Model Data
$kec                        // Kecamatan (lembaga info)
$kab                        // Kabupaten (area info)
$dir                        // User direktur (kepala unit)
$pinkel                     // PinjamanIndividu/Kelompok
$anggota                    // Anggota model
$trx                        // Transaksi model
$real                       // RealAngsuran (realization)
$ra                         // RencanaAngsuran (plan)

// Utilities
$keuangan                   // App\Utils\Keuangan
$tanggal                    // App\Utils\Tanggal (implicit)
Carbon\Carbon               // Date manipulation

// System Settings
$ttd                        // Digital signature/file
$logo                       // Lembaga logo path
$nama_lembaga               // Institution name
$tahun, $bulan, $hari      // Date variables
$type                       // Output format (pdf/html)
```

---

## 1️⃣2️⃣ UTILITIES & HELPER FUNCTIONS

### Tanggal Utility Class
```php
use App\Utils\Tanggal;

Tanggal::namaHari($date)          // e.g., "Senin"
Tanggal::hari($date)              // Day of month
Tanggal::namaBulan($date)         // e.g., "Januari"
Tanggal::tahun($date)             // Year
Tanggal::tglIndo($date)           // dd-Bulan-yyyy
Tanggal::tglLatin($date)          // dd/mm/yyyy
Tanggal::tglRomawi($date)         // Roman numerals
```

### Keuangan Utility Class
```php
$keuangan->terbilang($nominal)    // Numeric to text
$keuangan->pembulatan($value)     // Rounding per rules
$keuangan->saldoKas($date)        // Cash balance
$keuangan->saldoAwal($date, $akun) // Opening balance
```

### Carbon Helper
```php
Carbon::parse($date)->translatedFormat('d F Y')
Carbon::setLocale('id')           // Indonesian locale
```

---

## 1️⃣3️⃣ RESPONSE HANDLING PATTERNS

### Pattern 1: Direct View (HTML)
```php
return view('dokumen.template', $data);
```

### Pattern 2: PDF Stream
```php
$view = view('dokumen.template', $data)->render();
$pdf = PDF::loadHTML($view)->setPaper('A4', 'portrait');
return $pdf->stream();
```

### Pattern 3: PDF Download
```php
$pdf = PDF::loadHTML($view)->setPaper('A4', 'portrait');
return $pdf->download('dokumen_' . date('YmdHis') . '.pdf');
```

### Pattern 4: Window.print() (JavaScript)
```javascript
window.onload = function() {
    window.print();
}
```

---

## 1️⃣4️⃣ CHECKLIST UNTUK MEMBUAT DOKUMEN BARU

### Step-by-step Guide

**1. Create View File**
```blade
@php
    use App\Utils\Tanggal;
@endphp

@extends('dokumen.layout.base')
@section('content')
    <!-- Dokumen konten HTML -->
@endsection
```

**2. Create Controller Method**
```php
public function dokumentasi($id) {
    $data = Model::find($id);
    $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();
    $data['keuangan'] = new Keuangan();
    return view('path.to.dokumen', $data);
}
```

**3. Register Route**
```php
Route::get('/rute/dokumen/{id}', 'ControllerName@dokumentasi');
```

**4. Add Button in UI**
```html
<button class="btn btn-instagram" data-action="/rute/dokumen/{{ $id }}">
    <i class="fas fa-file"></i>
</button>
```

**5. CSS Styling**
- For print: Use `@media print { ... }`
- For specific size: Define custom paper size
- Font: Use standard fonts (Arial, Times, Courier)

---

## 1️⃣5️⃣ PRINT-SPECIFIC CSS

### Common Print Styles

```css
@media print {
    body {
        margin: 0;
        padding: 0;
        background: white;
    }
    
    @page {
        size: A4;
        margin: 0.5in;
    }
    
    .no-print {
        display: none;
    }
    
    .page-break {
        page-break-after: always;
    }
    
    table {
        border-collapse: collapse;
    }
    
    td, th {
        border: 1px solid #000;
        padding: 4px;
    }
}

/* Thermal printer 58mm */
.thermal {
    width: 58mm;
    font-size: 8px;
    font-family: monospace;
}

/* Dot matrix 80-column */
.matrix {
    width: 210mm;
    font-family: 'Courier New', monospace;
    font-size: 9px;
    line-height: 1.2;
}
```

---

## 1️⃣6️⃣ QUICK REFERENCE - DOKUMEN PER MODUL

### Dokumentasi Quick Lookup Table

| Dokumen | Locat
ion | Rute | Tombol? | Format | Kompleksitas |
|---------|--------|--------|--------|--------|----|
| SPK Individual | perguliran_i | `/perguliran_i/dokumen/spk/{id}` | Ya | PDF/HTML | High |
| SPK Kelompok | perguliran | `/perguliran/dokumen/spk/{id}` | Ya | PDF/HTML | High |
| Struk Angsuran | transaksi/jurnal_angsuran | `/transaksi/dokumen/struk/{id}` | Ya | HTML (58mm) | Medium |
| Arus Kas | pelaporan/view | `/pelaporan/arus_kas?tahun=X&bulan=Y` | Ya | PDF/HTML | High |
| Kartu Angsuran | perguliran_i | `/perguliran_i/dokumen/kartu_angsuran/{id}` | Ya | PDF/HTML | High |
| BKM | transaksi | `/transaksi/dokumen/bkm/{id}` | Ya | HTML (box) | Low |
| Simpanan Cetak | simpanan | `/simpanan/cetak_formulir/{id}` | Ya | HTML (auto-print) | Medium |

---

## 📌 KESIMPULAN

- **Total Dokumen**: 50+ file template
- **Total Rute**: 30+ rute dokumen di-map
- **Kategori**: 6 grup utama (Pinjaman Individual, Pinjaman Kelompok, Transaksi, Transaksi Angsuran, Pelaporan, Simpanan)
- **Response Format**: HTML, PDF, Print Dialog, Box Print (BKM/BKK/BM)
- **Controller**: Mostly PinjamanIndividuController, TransaksiController, PelaporanController
- **Output Media**: A4 Portrait, A4 Landscape, 14cm Box (BKM), 58mm Thermal, 80-column Matrix
- **Key Utilities**: Tanggal, Keuangan, Carbon, DOMPDF

**Status**: Production Ready  
**Last Updated**: 2024  
**Maintained By**: Development Team
