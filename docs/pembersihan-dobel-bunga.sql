-- =====================================================================
-- TAHAP 3 - QUERY PEMBERSIHAN DOBEL (READ-ONLY, untuk produksi)
-- =====================================================================
-- Ganti 351 dengan lokasi yang benar bila perlu.
-- TIDAK ADA perintah DELETE/UPDATE di file ini.
-- Jalankan per kategori, satu per satu, dan simpan output-nya sebagai
-- bukti sebelum melakukan koreksi.
-- =====================================================================


-- ---------------------------------------------------------------------
-- Q1. RINGKASAN: berapa kelompok dobel per kategori (untuk laporan)
-- ---------------------------------------------------------------------
SELECT 'transaksi_351 - BUNGA' AS kategori,
       COUNT(*) AS kelompok_dobel,
       SUM(n_baris) AS total_baris,
       SUM(n_baris - 1) AS baris_berlebih,
       SUM((n_baris - 1) * jumlah) AS nominal_berlebih
FROM (
    SELECT COUNT(*) AS n_baris, MAX(CAST(jumlah AS DECIMAL(20,2))) AS jumlah
    FROM transaksi_351
    WHERE deleted_at IS NULL
      AND id_simp IS NOT NULL AND id_simp <> '0'
      AND rekening_debit = '5.1.01.01'          -- akun bunga
      AND tgl_transaksi BETWEEN '2025-01-01' AND '2026-12-31'
    GROUP BY id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah
    HAVING COUNT(*) > 1
) x
UNION ALL
SELECT 'transaksi_351 - REAL_SIMPANAN (kode 2/3)',
       COUNT(*), SUM(n_baris), SUM(n_baris - 1), SUM((n_baris - 1) * nominal)
FROM (
    SELECT COUNT(*) AS n_baris,
           MAX(CAST(r.real_k + r.real_d AS DECIMAL(20,2))) AS nominal
    FROM real_simpanan_351 r
    WHERE r.kode IN (2, 3)
      AND r.tgl_transaksi BETWEEN '2025-01-01' AND '2026-12-31'
    GROUP BY r.cif, r.tgl_transaksi, r.kode
    HAVING COUNT(*) > 1
) y;


-- ---------------------------------------------------------------------
-- Q2. KATEGORI A - DOBEL BUNGA dari /bunga  (kandidat koreksi otomatis)
--     Dideteksi dari: id_simp sama, tgl_transaksi sama, akun 5.1.01.01->rek_simp,
--     jumlah sama. Termasuk yang 2024 (keterangan "Bunga SU ...") yang
--     berasal dari proses lama.
--     Output: idt yang DIPERTAHANKAN (terkecil) vs idt BERLEBIH.
-- ---------------------------------------------------------------------
SELECT t.idt,
       t.id_simp                                   AS rekening,
       t.tgl_transaksi,
       t.rekening_debit,
       t.rekening_kredit,
       t.jumlah,
       LEFT(t.keterangan_transaksi, 60)            AS keterangan,
       t.id_user,
       t.created_at,
       CASE WHEN t.idt = g.idt_pertahankan
            THEN 'PERTAHANKAN'
            ELSE 'BERLEBIH - kandidat koreksi'
       END AS status_koreksi,
       g.idt_pertahankan,
       g.n_baris
FROM transaksi_351 t
JOIN (
    SELECT id_simp,
           tgl_transaksi,
           rekening_debit,
           rekening_kredit,
           jumlah,
           MIN(idt)             AS idt_pertahankan,
           COUNT(*)             AS n_baris
    FROM transaksi_351
    WHERE deleted_at IS NULL
      AND id_simp IS NOT NULL AND id_simp <> '0'
      AND rekening_debit = '5.1.01.01'
      AND tgl_transaksi BETWEEN '2025-01-01' AND '2026-12-31'
    GROUP BY id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah
    HAVING COUNT(*) > 1
) g
  ON  g.id_simp        = t.id_simp
  AND g.tgl_transaksi = t.tgl_transaksi
  AND g.rekening_debit  = t.rekening_debit
  AND g.rekening_kredit = t.rekening_kredit
  AND g.jumlah         = t.jumlah
WHERE t.deleted_at IS NULL
ORDER BY t.id_simp, t.tgl_transaksi, t.idt;


-- ---------------------------------------------------------------------
-- Q3. KATEGORI B - DOBEL REAL_SIMPANAN kode 2 (Setoran) dan 3 (Penarikan)
--     PENTING: ini BUKAN dari /bunga. Bisa jadi sah (setor/penarikan
--     dengan nominal sama di hari yang sama dicatat dua kali oleh operator).
--     WAJIB diverifikasi ke operator/slip sebelum koreksi.
--     Output: id real_simpanan yang dipertahankan vs berlebih.
-- ---------------------------------------------------------------------
SELECT r.id,
       r.cif                                        AS rekening,
       r.tgl_transaksi,
       r.kode,
       CASE r.kode WHEN 2 THEN 'Setoran' WHEN 3 THEN 'Penarikan' END AS jenis,
       r.real_d,
       r.real_k,
       r.`sum`,
       r.lu,
       r.id_user,
       t.idt                                        AS idt_transaksi,
       t.rekening_debit,
       t.rekening_kredit,
       t.jumlah,
       LEFT(t.keterangan_transaksi, 60)             AS keterangan,
       CASE WHEN r.id = g.id_pertahankan
            THEN 'PERTAHANKAN'
            ELSE 'BERLEBIH - VERIFIKASI OPERATOR DULU'
       END AS status_koreksi,
       g.id_pertahankan,
       g.n_baris
FROM real_simpanan_351 r
JOIN (
    SELECT cif, tgl_transaksi, kode, MIN(id) AS id_pertahankan, COUNT(*) AS n_baris
    FROM real_simpanan_351
    WHERE kode IN (2, 3)
      AND tgl_transaksi BETWEEN '2025-01-01' AND '2026-12-31'
    GROUP BY cif, tgl_transaksi, kode
    HAVING COUNT(*) > 1
) g
  ON  g.cif           = r.cif
  AND g.tgl_transaksi = r.tgl_transaksi
  AND g.kode          = r.kode
LEFT JOIN transaksi_351 t ON t.idt = r.idt
ORDER BY g.n_baris DESC, r.cif, r.id;


-- ---------------------------------------------------------------------
-- Q4. TRANSAKSI YITIM: transaksi aktif tanpa baris real_simpanan
--     (akibat insert real_simpanan gagal -> saldo rantai tidak sinkron)
--     Semua hasil proses /bunga yang gagal harus ikut dikoreksi.
-- ---------------------------------------------------------------------
SELECT t.idt, t.id_simp AS rekening, t.tgl_transaksi,
       t.rekening_debit, t.rekening_kredit, t.jumlah,
       LEFT(t.keterangan_transaksi, 60) AS keterangan,
       t.id_user, t.created_at
FROM transaksi_351 t
LEFT JOIN real_simpanan_351 r ON r.idt = t.idt
WHERE t.deleted_at IS NULL
  AND t.id_simp IS NOT NULL AND t.id_simp <> '0'
  AND t.rekening_debit = '5.1.01.01'
  AND r.id IS NULL
ORDER BY t.tgl_transaksi, t.idt;


-- ---------------------------------------------------------------------
-- Q5. DESYNC idt: real_simpan_351 menunjuk idt transaksi yang tidak ada
--     (akibat id max(idt)+1 manual pada kode lama)
-- ---------------------------------------------------------------------
SELECT r.id, r.cif, r.idt AS idt_tidak_ada, r.kode, r.tgl_transaksi,
       r.real_d, r.real_k, r.`sum`, r.lu
FROM real_simpanan_351 r
LEFT JOIN transaksi_351 t ON t.idt = r.idt
WHERE t.idt IS NULL
ORDER BY r.id;


-- ---------------------------------------------------------------------
-- Q6. VERIFIKASI SETELAH KOREKSI (jalankan setelah perbaikan)
-- ---------------------------------------------------------------------
-- 6a. Sisa dobel bunga (harus 0)
SELECT COUNT(*) AS sisa_dobel_bunga FROM (
    SELECT id_simp, tgl_transaksi, rekening_debit, rekening_kredit, jumlah, COUNT(*) c
    FROM transaksi_351
    WHERE deleted_at IS NULL AND id_simp IS NOT NULL AND id_simp <> '0'
      AND rekening_debit = '5.1.01.01'
    GROUP BY 1,2,3,4,5 HAVING c > 1
) x;

-- 6b. Transaksi yatim (harus 0)
SELECT COUNT(*) AS sisa_transaksi_yatim
FROM transaksi_351 t
LEFT JOIN real_simpanan_351 r ON r.idt = t.idt
WHERE t.deleted_at IS NULL AND t.id_simp IS NOT NULL AND t.id_simp <> '0'
  AND t.rekening_debit = '5.1.01.01' AND r.id IS NULL;

-- 6c. Total bunga bulan yang dikoreksi vs yang diharapkan
SELECT tgl_transaksi, COUNT(*) AS n_rekening, SUM(CAST(jumlah AS DECIMAL(20,2))) AS total_bunga
FROM transaksi_351
WHERE deleted_at IS NULL AND rekening_debit = '5.1.01.01'
  AND tgl_transaksi = '2026-06-25'
GROUP BY tgl_transaksi;

-- 6d. Cek saldo_351 vs rekap transaksi (trigger saldo_ menghitung SEMUA baris,
--     termasuk yang soft-deleted -> cocokkan dengan deleted_at IS NULL)
SELECT s.kode_akun, s.tahun, s.bulan, s.debit AS saldo_tersimpan,
       (SELECT IFNULL(SUM(t.jumlah),0) FROM transaksi_351 t
         WHERE t.rekening_debit = s.kode_akun AND t.deleted_at IS NULL
           AND t.tgl_transaksi BETWEEN CONCAT(s.tahun,'-01-01')
               AND LAST_DAY(CONCAT(s.tahun,'-',s.bulan,'-01'))) AS debit_transaksi_aktif
FROM saldo_351 s
WHERE s.tahun = 2026 AND s.bulan = '06'
  AND s.kode_akun IN ('5.1.01.01','2.1.04.01','2.1.03.01','4.1.03.01')
ORDER BY s.kode_akun;

-- 6e. Cek real_simpanan: sum harus konsisten dengan rantai
--     (cari baris yang 'sum' tidak sama dengan sum baris sebelumnya + d - k)
SELECT r.cif, r.tgl_transaksi, r.id, r.`sum`,
       (SELECT IFNULL(prev.`sum`,0) + r.real_d - r.real_k
          FROM real_simpanan_351 prev
         WHERE prev.cif = r.cif AND prev.id < r.id
         ORDER BY prev.id DESC LIMIT 1) AS sum_harapan
FROM real_simpanan_351 r
WHERE r.cif IN (169, 1065, 1731) AND r.tgl_transaksi >= '2024-01-01'
ORDER BY r.cif, r.id;
