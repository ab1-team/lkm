# TAHAP 3 — Rancangan Pembersihan Transaksi Dobel (READ-ONLY, BELUM DIJALANKAN)

> **Status: RANCANGAN SAJA. Tidak ada data yang diubah, tidak ada DELETE/UPDATE yang dieksekusi.**
> Dokumen ini untuk dipakai **pihak berwenang di produksi**, bukan oleh agent.
> Semua angka di bawah berasal dari `lkm_dump` (SELECT only), lokasi 351.

---

## 0. Ringkasan Eksekutif

| Kategori | Sumber | Kelompok dobel | Baris berlebih | Nominal berlebih | Otomatis koreksi? |
|---|---|---|---|---|---|
| **A. Dobel bunga** | `/bunga` (atau proses lama 2024) | 3 | 3 | Rp 23.083 | **Ya**, setelah verifikasi |
| **B. Dobel setor/penarikan** (`real_simpanan` kode 2/3) | input manual operator | 233 | 244 | Rp 1.505.438.112 | **Tidak** — wajib verifikasi operator |
| **C. Transaksi yatim** | `/bunga` gagal di tengah | 2 | 2 | Rp 31.363 | **Ya**, setelah verifikasi |
| **D. Desync `idt`** | bug `max(idt)+1` di kode lama | 11 | 11 | — | **Ya** (regenerasi) |

**Penting: kategori A dan B harus diperlakukan BEDA.**

- **Kategori A (dobel bunga)** adalah **bug aplikasi**. Ciri: `rekening_debit = 5.1.01.01` (akun Pendapatan Bunga), `id_simp` sama, `tgl_transaksi` sama, `jumlah` identik, `created_at` berdekatan. Didua proses `/bunga` yang tumpang tindih. **Dapat dikoreksi otomatis.**
- **Kategori B (dobel setor/penarikan)** **BUKAN** dari `/bunga`. Ini `real_simpanan` kode 2 (Setoran) dan 3 (Penarikan) dengan nominal **berbeda-beda** dalam satu hari pada satu rekening (contoh: cif 165, 2025-06-30 → Rp 500.000 + Rp 100.000 + Rp 80.000). Nominal berbeda = **cenderung sah**: operator mencatat 3 setoran terpisah. Menghapus tanpa verifikasi slip kas akan **merusak saldo**. Wajib konfirmasi operator.

---

## 1. Bukti Penyebab (Category A)

Pada `transaksi_351` ada **3 kelompok** dobel bunga (semua `deleted_at IS NULL`):

| idt | rekening | tgl_transaksi | akun | jumlah | keterangan | created_at | id_user |
|---|---|---|---|---|---|---|---|
| **42576** | 169 | 2026-06-25 | 5.1.01.01→2.1.04.01 | 16218 | `Bunga 0351.221.000169 WA AGO BUTON bulan Juni 2026` | 2026-06-26 17:45:15 | 137 |
| **42577** | 169 | 2026-06-25 | 5.1.01.01→2.1.04.01 | 16218 | (identik) | 2026-06-26 17:45:15 | 137 |
| 11193 | 1065 | 2024-06-25 | 5.1.01.01→2.1.04.01 | 4252 | `Bunga SU  JUITA UMASUGI Juni` | NULL | 136 |
| 11194 | 1065 | 2024-06-25 | 5.1.01.01→2.1.04.01 | 4252 | (identik) | NULL | 136 |
| 9685 | 1731 | 2024-04-26 | 5.1.01.01→2.1.04.01 | 2613 | `Bunga SU  ENDANG ROSIANA SAKLARESSY April` | NULL | 136 |
| 9690 | 1731 | 2024-04-26 | 5.1.01.01→2.1.04.01 | 2613 | (identik) | NULL | 136 |

Ciri yang membedakan dari transaksi sah:
1. `idt` **berurutan** (42576→42577, 11193→11194, 9685→9690) — dua insert beruntun.
2. `created_at` **selisih 0 detik** untuk kasus 2026 (dua proses, bukan dua klik berurutan — satu proses butuh ±1,3 detik/rekening).
3. `keterangan_transaksi` **persis sama** termasuk nama & bulan.
4. Untuk kasus 2026, `id_simp` **tidak boleh** dihitung ganda dalam `real_simpanan` → saldo bulan Juni 2026 overstated Rp 16.218.

> Catatan: pola keterangan `Bunga SU ...` **tidak ada di source code repository** (`grep -rn "Bunga SU" app/ resources/` → 0 hasil) dan mendahului fitur `/bunga` (rilis 2025-04-26). Dua kasus 2024 berasal dari **proses lama/manual**, bukan dari bug `/bunga` yang baru diperbaiki. nevertheless Episod yang sama (cek-lalu-insert tanpa lock) juga berlaku di sana.

---

## 2. Trigger `saldo_{N}` — perilaku yang HARUS diketahui (sudah diuji empiris)

Trigger yang berlaku: `create_saldo_debit_351`, `update_saldo_debit_351`, `delete_saldo_debit_351` (AFTER INSERT / UPDATE / DELETE pada `transaksi_351`).

**Hasil uji di database uji `lkm_test_bunga` (tabel & trigger disalin dari dump):**

| Langkah | `saldo_351.debit` (5.1.01.01, 2026-12) | Σ transaksi aktif | Σ transaksi semua |
|---|---|---|---|
| Setelah INSERT 777.777 | 5.569.940 | 777.777 | 777.777 |
| Setelah **SOFT DELETE** | **5.569.940 (TIDAK BERUBAH)** | 0 | 777.777 |
| Setelah **HARD DELETE** | **4.792.163 (berhasil dikoreksi)** | 0 | 0 |

### ⚠️ Konsekuensi menentukan

1. **Soft delete TIDAK memperbaiki `saldo_{N}`.** Trigger menghitung `SUM(jumlah)` dari **semua** baris tanpa filter `deleted_at IS NULL`. Jadi kalau baris di-soft-delete, saldo bulanan tetap ikut terhitung → laporan neraca/laba-rugi tetap salah.
2. **Hard delete MEMPERBAIKI `saldo_{N}`** — trigger `AFTER DELETE` menghitung ulang.
3. Maka ada **dua jalur koreksi**, dan harus dipilih per kasus (lihat §4).
4. Konsekuensi lanjutan: kalau memilih **soft delete**, `saldo_{N}` **wajib dihitung ulang manual** setelahnya (query di §5 Q6d).
5. Perbedaan `saldo_351` vs rekap transaksi pada `1.1.01.01` Juni 2026: saldo tersimpan `8.145.843.549,39` vs transaksi aktif `8.145.843.549,39` vs transaksi semua `8.181.179.549,39` → ada selisih Rp 35.336.000 dari 20 baris soft-deleted. **Selisih ini akan membuat laporan salah bila soft delete dipakai tanpa regenerasi `saldo_{N}`.**

---

## 3. Query SELECT (siap pakai, read-only)

Berkas lengkap: `docs/pembersihan-dobel-bunga.sql` (disalin ke repo). Ringkas:

### Q1 — Ringkasan per kategori
```sql
SELECT 'transaksi_351 - BUNGA' AS kategori, COUNT(*) AS kelompok_dobel,
       SUM(n_baris) AS total_baris, SUM(n_baris - 1) AS baris_berlebih,
       SUM((n_baris - 1) * jumlah) AS nominal_berlebih
FROM (
    SELECT COUNT(*) AS n_baris, MAX(CAST(jumlah AS DECIMAL(20,2))) AS jumlah
    FROM transaksi_351
    WHERE deleted_at IS NULL AND id_simp IS NOT NULL AND id_simp <> '0'
      AND rekening_debit = '5.1.01.01'
      AND tgl_transaksi BETWEEN '2024-01-01' AND '2026-12-31'
    GROUP BY id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah
    HAVING COUNT(*) > 1
) x
UNION ALL
SELECT 'real_simpanan_351 - kode 2/3 (SETOR/TARIK)',
       COUNT(*), SUM(n_baris), SUM(n_baris - 1), SUM((n_baris - 1) * nominal)
FROM (
    SELECT COUNT(*) AS n_baris, MAX(CAST(r.real_k + r.real_d AS DECIMAL(20,2))) AS nominal
    FROM real_simpanan_351 r
    WHERE r.kode IN (2, 3)
    GROUP BY r.cif, r.tgl_transaksi, r.kode
    HAVING COUNT(*) > 1
) y;
```
Hasil di dump: **BUNGA → 3 kelompok / 3 baris berlebih / Rp 23.083** · **KODE 2/3 → 233 kelompok / 244 baris berlebih / Rp 1.505.438.112**.

### Q2 — KATEGORI A: daftar dobel bunga + penanda dipertahankan/berlebih
```sql
SELECT t.idt, t.id_simp AS rekening, t.tgl_transaksi,
       t.rekening_debit, t.rekening_kredit, t.jumlah,
       LEFT(t.keterangan_transaksi, 60) AS keterangan, t.id_user, t.created_at,
       CASE WHEN t.idt = g.idt_pertahankan THEN 'PERTAHANKAN'
            ELSE 'BERLEBIH - kandidat koreksi' END AS status_koreksi,
       g.idt_pertahankan, g.n_baris
FROM transaksi_351 t
JOIN (
    SELECT id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah,
           MIN(idt) AS idt_pertahankan, COUNT(*) AS n_baris
    FROM transaksi_351
    WHERE deleted_at IS NULL AND id_simp IS NOT NULL AND id_simp <> '0'
      AND rekening_debit = '5.1.01.01'
      AND tgl_transaksi BETWEEN '2024-01-01' AND '2026-12-31'
    GROUP BY id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah
    HAVING COUNT(*) > 1
) g
  ON  g.id_simp = t.id_simp AND g.tgl_transaksi = t.tgl_transaksi
  AND g.rekening_debit = t.rekening_debit AND g.rekening_kredit = t.rekening_kredit
  AND g.jumlah = t.jumlah
WHERE t.deleted_at IS NULL
ORDER BY t.id_simp, t.tgl_transaksi, t.idt;
```
**Aturan "dipertahankan": `MIN(idt)`** (baris paling awal) — barring ada bukti lain.

### Q3 — KATEGORI B: daftar dobel `real_simpanan` kode 2/3 + penanda
```sql
SELECT r.id, r.cif AS rekening, r.tgl_transaksi, r.kode,
       CASE r.kode WHEN 2 THEN 'Setoran' WHEN 3 THEN 'Penarikan' END AS jenis,
       r.real_d, r.real_k, r.`sum`, r.lu, r.id_user,
       t.idt AS idt_transaksi, t.rekening_debit, t.rekening_kredit, t.jumlah,
       LEFT(t.keterangan_transaksi, 60) AS keterangan,
       CASE WHEN r.id = g.id_pertahankan THEN 'PERTAHANKAN'
            ELSE 'BERLEBIH - VERIFIKASI OPERATOR DULU' END AS status_koreksi,
       g.id_pertahankan, g.n_baris
FROM real_simpanan_351 r
JOIN (
    SELECT cif, tgl_transaksi, kode, MIN(id) AS id_pertahankan, COUNT(*) AS n_baris
    FROM real_simpanan_351
    WHERE kode IN (2, 3)
    GROUP BY cif, tgl_transaksi, kode
    HAVING COUNT(*) > 1
) g ON g.cif = r.cif AND g.tgl_transaksi = r.tgl_transaksi AND g.kode = r.kode
LEFT JOIN transaksi_351 t ON t.idt = r.idt
ORDER BY g.n_baris DESC, r.cif, r.id;
```
> Untuk kategori B, `MIN(id)` **hanya titik awal verifikasi**, bukan keputusan. Contoh yang wajib dicek operator:
> - `cif 1622 / 2025-12-23 / kode 3` → Rp 10.000.000 + Rp 10.000 + Rp 1.000.000 (3 tarikan berbeda)
> - `cif 165 / 2025-06-30 / kode 2` → Rp 500.000 + Rp 100.000 + Rp 80.000
> - `cif 2762 / 2025-12-29 / kode 2` → Rp 50.000.000 + Rp 30.000.000 + Rp 20.000.000
> - `cif 2451 / 2025-04-29 / kode 2` → "Setoran Awal" Rp 25.000.000 + setoran Rp 9.900.000 + Rp 100.000
>
> Dua dari empat contoh involve **"Setoran Awal"** — jelas bukan duplikat, melainkan pembukaan rekening + setoran susulan.

### Q4 — Transaksi yitim (transaksi aktif tanpa `real_simpanan`)
```sql
SELECT t.idt, t.id_simp AS rekening, t.tgl_transaksi,
       t.rekening_debit, t.rekening_kredit, t.jumlah,
       LEFT(t.keterangan_transaksi, 60) AS keterangan, t.id_user, t.created_at
FROM transaksi_351 t
LEFT JOIN real_slipidemia_351 r ON r.idt = t.idt
WHERE t.deleted_at IS NULL AND t.id_simp IS NOT NULL AND t.id_simp <> '0'
  AND t.rekening_debit = '5.1.01.01' AND r.id IS NULL
ORDER BY t.tgl_transaksi, t.idt;
```
Hasil di dump: **2 baris** — `idt 33774` (cif 202, 2025-12-25, Rp 4.520) dan `idt 44044` (cif 965, 2026-07-25, Rp 28.543).

### Q5 — Desync `idt` (`real_simpanan.idt` menunjuk idt yang tidak ada)
```sql
SELECT r.id, r.cif, r.idt AS idt_tidak_ada, r.kode, r.tgl_transaksi,
       r.real_d, r.real_k, r.`sum`, r.lu
FROM real_simpanan_351 r
LEFT JOIN transaksi_351 t ON t.idt = r.idt
WHERE t.idt IS NULL
ORDER BY r.id;
```
Hasil di dump: **11 baris**. Contoh `r.id 50136` (cif 965, kode 5, 2026-07-25) menunjuk `idt 44043` yang **tidak ada**; bunga cif 965 sebenarnya ada di `idt 44044`. Iniigan hasil bug `real_simpanan.idt = Transaksi::max('idt') + 1` manual.

---

## 4. Strategi Koreksi (aman, tanpa hard delete untuk data yang perlu diaudit)

### 4.1 Prinsip
1. **Backup penuh dulu** (lihat §4.5). Koreksi tanpa backup = berisiko.
2. **Tidak ada hard delete** pada transaksi yang masih diaudit. Gunakan **soft delete** atau **jurnal pembalik**.
3. Kalau memilih soft delete → **wajib regenerasi `saldo_{N}`** (§4.4), karena trigger tidak memperbaiki saldo untuk soft delete.
4. **Satu rekening satu waktu.** Jangan mengoreksi lebih dari satu rekening dalam satu transaksi, supaya mudah rollback.
5. Simpan **bukti sebelum/sesudah** untuk setiap rekening yang dikoreksi.

### 4.2 Kategori A — Dobel bunga

**Pilihan yang disarankan: JURNAL PEMBALIK (bukan soft delete, bukan hard delete).**

Alasan:
- Soft delete **tidak memperbaiki** `saldo_{N}` (terbukti §2) → harus ada langkah regenerasi saldo tambahan.
- Hard delete menghapus bukti audit.
- Jurnal pembalik menulis transaksi pasangan dengan `tgl_transaksi` sama, `jumlah` sama, akun terbalik (kredit↔debit), `id_simp` sama, dan `keterangan` ditandai `"KOREKSI BUNGA DOBEL ..."`. Tabel `kode_simp` sudah punya kode 8 = "Koreksi Kredit" dan kode 9 = "Koreksi Debet" → **mekanisme koreksi resmi** sudah ada di aplikasi.

Langkah ( contoh untuk idt 42577 yang berlebih):
```
1. Verifikasi idt 42576 & 42577 benar-benar duplikat (keterangan, jumlah, rekening, tanggal).
2. Catat saldo_351 sebelum koreksi (akun 5.1.01.01 & 2.1.04.01, tahun 2026, bulan '06').
3. Insert transaksi pembalik:
     rekening_debit  = 2.1.04.01   (kreditkan bunga)
     rekening_kredit = 5.1.01.01   (debitkan akun bunga)
     id_simp          = 169
     tgl_transaksi    = 2026-06-25  (tanggal yang sama -> saldo bulan sama)
     jumlah           = 16218
     keterangan       = "Koreksi Bunga Dobel - Bunga 0351.221.000169 WA AGO BUTON bulan Juni 2026 (idt 42577)"
     id_user          = <id operator executing>
4. Trigger AFTER INSERT otomatis memperbarui saldo_351.
5. Insert baris real_simpanan pasangan:
     idt = <idt transaksi pembalik>, kode = 0, real_d = 16218, real_k = 0,
     sum = <sum baris sebelumnya> - 16218  -> gunakan nilai `sum` yang benar,
     tgl_transaksi = 2026-06-25
   (paling aman: hitung ulang `sum` dengan urutan id, lihat §4.4)
6. Verifikasi: total bunga Juni 2026 turun Rp 16.218 (16218 -> 0 untuk rekening 169),
   saldo_351 akun 5.1.01.01 & 2.1.04.01 turun Rp 16.218.
```
> Bila operator lebih memilih **soft delete**, gunakan `UPDATE transaksi_351 SET deleted_at = NOW() WHERE idt = 42577` **DAN** langsung lanjut ke §4.4 (regenerasi saldo) — tanpa itu, laporan neraca tetap salah.

### 4.3 Kategori B — Dobel setor/penarikan (WAJIB VERIFIKASI OPERATOR)

**Jangan dihapus otomatis.** Nominal berbeda-beda → kemungkinan besar sah.

Langkah:
1. Ambil daftar via **Q3**.
2. Untuk tiap kelompok, minta operator mencocokkan dengan **slip kas / buku tunai** tanggal tersebut: berapa kali uang benar-benar masuk/keluar, dan berapa nominalnya.
3. Hanya kelompok yang **dikonfirmasi duplikat** (nominal identik persis, 2 baris dengan nominal sama) yang masuk daftar koreksi.
4. Untuk yang terkonfirmasi: gunakan **jurnal pembalik** (bukan hapus) agar saldo & saldo_ tetap konsisten, atau soft delete + regenerasi `real_simpanan.sum` + `saldo_{N}`.
5. Yang **dikonfirmasi sah** → **jangan diubah**. Daftarkan sebagai "sah, tidak dikoreksi" di laporan agar tidak diperiksa berulang.

### 4.4 Hitung ulang `real_simpanan.sum` dan `saldo_{N}`

**`real_simpanan.sum` = saldo berjalan (saldo baris sebelumnya + real_k − real_d).**
Setelah koreksi, `sum` baris-baris berikutnya untuk rekening yang sama **ikut berubah** karena memuat saldo kumulatif. Jadi koreksi 1 baris berarti **seluruh baris setelahnya** untuk rekening itu harus dihitung ulang.

Hitung ulang per rekening (hanya baris dengan `idt` yang valid & transaksi tidak soft-deleted):
```sql
-- CONTOH (BACAL DULU untuk melihat nilai slated, lalu UPDATE per IDF)
SELECT r.cif, r.id, r.tgl_transaksi, r.`sum` AS sum_sekarang,
       SUM(COALESCE(t.jumlah,0)) OVER (
           PARTITION BY r.cif ORDER BY r.id
           ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
       ) AS sum_hasil_hitung_ulang
FROM real_simpanan_351 r
LEFT JOIN transaksi_351 t
       ON t.idt = r.idt AND t.deleted_at IS NULL
WHERE r.cif = <REKENING>
ORDER BY r.id;
```
> Perhatikan: `real_simpanan` memuat baris `kode=0` (tanpa relasi idt ke transaksi yang valid) — baris itu tetap dihitung sebagai bagian dari saldo berjalan. **Pastikan urutan `id` = urutan kronologis**, karena `sum` dihitung berurutan `id`.

**Regenerasi `saldo_{N}`** — dua cara:
- **Cara A (disarankan):** panggil trigger dengan cara me-`UPDATE`/`INSERT` baris yang relevan → trigger menghitung ulang otomatis. Contoh: `UPDATE transaksi_351 SET updated_at = updated_at WHERE idt = <id>` (memicu `update_saldo_debit_351`).
- **Cara B:** hitung ulang manual `saldo_{N}` dari `transaksi_351` (query Q6d) lalu `INSERT ... ON DUPLICATE KEY UPDATE`.

> **Jangan** `TRUNCATE saldo_{N}` di produksi kecuali seluruh `saldo_{N}` dihitung ulang lengkap — `saldo_{N}` juga dipakai modul lain.

### 4.5 Backup sebelum koreksi (WAJIB)

```bash
# 1. Dump tabel yang akan disentuh (pada server produksi)
mysqldump -u <user> -p <db> transaksi_351 real_simpanan_351 saldo_351 > backup_bunga_$(date +%F_%H%M%S).sql

# 2. Backup baris yang akan dikoreksi saja (lebih kecil, berguna untuk audit)
mysqldump -u <user> -p <db> transaksi_351  --where="idt IN (42577,11194,9690)" > backup_rows_bunga.sql
mysqldump -u <user> -p <db> real_simpanan_351 --where="id IN (...)" > backup_rows_real.sql

# 3. Verifikasi file dump bukan nol & bisa dibaca
ls -la backup_bunga_*.sql && head -5 backup_bunga_*.sql
```
> **`lkm_dump` lokal tidak boleh dipakai untuk koreksi.** Semua di atas dijalankan di server produksi oleh pihak berwenang.

### 4.6 Verifikasi sesudahnya (wajib, semua harus cocok)

```sql
-- 6a. Sisa dobel bunga -> harus 0
SELECT COUNT(*) FROM (
  SELECT id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah, COUNT(*) c
  FROM transaksi_351 WHERE deleted_at IS NULL AND id_simp IS NOT NULL AND id_simp <> '0'
    AND rekening_debit = '5.1.01.01'
  GROUP BY 1,2,3,4,5 HAVING c > 1) x;

-- 6b. Transaksi yatim -> harus 0 (atau disesuaikan dengan daftar yang disetujui)
SELECT COUNT(*) FROM transaksi_351 t
LEFT JOIN real_simpanan_351 r ON r.idt = t.idt
WHERE t.deleted_at IS NULL AND t.id_simp IS NOT NULL AND t.id_simp <> '0'
  AND t.rekening_debit = '5.1.01.01' AND r.id IS NULL;

-- 6c. Total bunga per bulan (bandingkan dengan daftar yang disetujui)
SELECT tgl_transaksi, COUNT(*) AS n_rekening, SUM(CAST(jumlah AS DECIMAL(20,2))) AS total_bunga
FROM transaksi_351
WHERE deleted_at IS NULL AND rekening_debit = '5.1.01.01'
GROUP BY tgl_transaksi ORDER BY tgl_transaksi;

-- 6d. saldo_351 vs rekap transaksi AKTIF (harus sama persis)
SELECT s.kode_akun, s.tahun, s.bulan, s.debit AS saldo_tersimpan,
       (SELECT IFNULL(SUM(t.jumlah),0) FROM transaksi_351 t
         WHERE t.rekening_debit = s.kode_akun AND t.deleted_at IS NULL
           AND t.tgl_transaksi BETWEEN CONCAT(s.tahun,'-01-01')
               AND LAST_DAY(CONCAT(s.tahun,'-',s.bulan,'-01'))) AS debit_aktif
FROM saldo_351 s WHERE s.tahun = 2026 AND s.bulan = '06'
  AND s.kode_akun IN ('5.1.01.01','2.1.04.01','2.1.03.01','4.1.03.01')
ORDER BY s.kode_akun;

-- 6e. Rantai real_simpan_351 per rekening koreksi (sum harus konsisten)
--      (query jendela di §4.4, jalankan ulang dan bandingkan)
```

**Verifikasi.business** — setelah koreksi saldo, mohon **cetak ulang & bandingkan**:
1. Laporan **Neraca** dan **Laba Rugi** untuk periode yang dikoreksi (bulan 06/2026 dsb).
2. **Kartu rekening** (cetak pada buku) untuk rekening 169, 1065, 1731 — saldo akhir & mutasi bulanan harus sesuai.
3. Laporan **OJK** terkait (tabungan & penempatan dana).
4. **Total bunga** bulan itu vs daftar yang disetujui operator.

---

## 5. Yang TIDAK boleh dikerjakan

- ❌ Hard delete transaksi yang masih perlu diaudit → gunakan jurnal pembalik / soft delete.
- ❌ `TRUNCATE` tabel `transaksi_{N}`, `real_simpanan_{N}`, atau `saldo_{N}` di produksi.
- ❌ Menghapus dobel kategori B (setor/tarik) tanpa verifikasi slip kas.
- ❌ Mengubah engine `transaksi_{N}` (MyISAM → InnoDB) sebagai bagian dari pembersihan — itu perubahan infrastruktur, terpisah dari tugas ini, dan punya implikasi performa & locking di aplikasi yang sedang dipakai.
- ❌ Menjalankan query di atas pada `lkm_dump` lokal (hanya SELECT).

---

## 6. Urutan Kerja yang Disarankan

| # | Langkah | Hasil yang diharapkan |
|---|---|---|
| 1 | Jalankan Q1–Q5 di produksi, simpan output | Daftar dobel aktual di produksi (bisa berbeda dari dump) |
| 2 | Backup penuh (mysqldump 3 tabel) | File backup terverifikasi |
| 3 | Kategory A: konfirmasi 3 kelompok dobel bunga | Daftar final idt yang dikoreksi |
| 4 | Kategori A: koreksi **satu rekening satu kali** (jurnal pembalik) | saldo_351 & real_simpanan_351 ikut benar |
| 5 | Kategori C: koreksi 2 transaksi yatim | Tidak ada transaksi tanpa real_simpanan |
| 6 | Kategori D: regenerasi `real_simpanan` untuk 11 baris desync + hitung ulang `sum` | `real_simpanan.idt` selalu valid |
| 7 | **Kategori B: verifikasi operator dulu** (tidak boleh dilewati) | Daftar sah vs duplikat |
| 8 | Koreksi hanya yang terkonfirmasi duplikat | Setoran sah tetap utuh |
| 9 | Jalankan verifikasi 6a–6e | Semua 0 / cocok |
| 10 | Cetak ulang neraca, laba-rugi, kartu rekening, laporan OJK | Angka konsisten |
| 11 | Dokumentasikan di laporan Tsch: rekening, idt, nominal, operator, sebelum/sesudah | Audit trail |
