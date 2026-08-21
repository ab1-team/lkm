# Dokumentasi PTK_POJK — Penilaian Tingkat Kesehatan POJK

## Deskripsi

`PTK_POJK` adalah modul pelaporan untuk **Penilaian Tingkat Kesehatan Lembaga Keuangan Mikro (LKM)** berdasarkan parameter **POJK** dan pembobotan **SEOJK No. 21/SEOJK.06/2015**.

Modul ini menghasilkan:
1. Laporan komposit skor 5 aspek (Permodalan, Kualitas Aset, Manajemen, Rentabilitas, Likuiditas)
2. Peringkat Komposit (PK) 1–5 (Sangat Sehat → Tidak Sehat)
3. Status Pengawasan OJK (Normal / Intensif / Khusus)
4. Rekomendasi perbaikan otomatis per rasio

---

## Lokasi File

| File | Deskripsi |
|---|---|
| `app/Http/Controllers/PelaporanController.php` (line 5499–5825) | Method `PTK_POJK()` — controller pelaporan |
| `app/Utils/Keuangan.php` (line 613–836) | Helper `tingkat_kesehatan()` — hitung kolek, NPL, PPAP |
| `resources/views/pelaporan/view/pojk/penilaian_tingkat_kesehatan.blade.php` | View laporan (PDF / HTML) |

---

## Cara Pemanggilan

Method dipanggil lewat dispatcher utama di `PelaporanController.php` (line 308–317):
```php
$file = $request->laporan;
if ($file == 3) {
    // ...
} elseif ($file == 20 || $file == 21) {
    $file = $request->sub_laporan;
    $result = $this->$file($data);
}
```

**Aktivasi dari form/UI**: kirim `laporan=20` (atau `21`) dan `sub_laporan=PTK_POJK`.

> ⚠️ Catatan: nama method saat ini `PTK_POJK` (uppercase). Disarankan rename ke `ptk_pojk` (snake_case lowercase) agar konsisten dengan method lain seperti `kolek_kelompok_mingguan`.

---

## Alur Data

```
Request (tahun, bulan, hari, type)
        │
        ▼
PelaporanController::show()  ← set $data (tgl_kondisi, sub_judul, dll)
        │
        ▼
PelaporanController::PTK_POJK($data)
        │
        ├── Keuangan::aset()                  → total_aset, cadangan_piutang
        ├── Keuangan::tingkat_kesehatan()     → kolek_items, sum_kolek_total,
        │                                        saldo_pokok (outstanding), PPAP
        ├── Keuangan::pendapatan() - biaya()  → laba_bersih, ROA
        ├── Keuangan::modal_awal()            → modal_disetor
        │
        ▼
Hitung 5 Rasio + Skor + Peringkat Komposit + Status Pengawasan
        │
        ▼
Render view penilaian_tingkat_kesehatan.blade.php
        │
        ├── type='pdf'  → DOMPDF stream
        └── type='html' → return HTML
```

---

## Sumber Data

### Data Otomatis (parameter keuangan)
- **Total Aset, Liabilitas, Ekuitas, Kas & Setara Kas** — dari helper `Keuangan::komSaldo()` via `Rekening` (akun 1.x, 2.x, 3.x)
- **Cadangan Piutang (PPAP terbentuk)** — dari helper `Keuangan::aset()` (akun 1.1.14)
- **Outstanding Pinjaman & Kolektibilitas** — dari helper `Keuangan::tingkat_kesehatan()` via `PinjamanIndividu`
- **Pendapatan & Biaya** — dari helper `Keuangan::pendapatan()` dan `Keuangan::biaya()` (rekening 4.x dan 5.x)
- **Modal Disetor** — dari helper `Keuangan::modal_awal()`

### Data Input (dari request)
- `tahun`, `bulan`, `hari` → `tgl_kondisi`
- `bulanan` (bool) → flag periode bulanan vs tahunan
- `type` (`pdf` / `html`)
- `kec` → instance Kecamatan

---

## 5 Aspek Penilaian (Bobot SEOJK)

| # | Aspek | Bobot | Sub-Rasio |
|---|---|---|---|
| 1 | Permodalan & Solvabilitas | 25% | Rasio Solvabilitas (12,5%), Rasio Ekuitas (12,5%) |
| 2 | Kualitas Aset | 35% | NPL Neto (21%), Coverage PPAP (14%) |
| 3 | Manajemen | 20% | Penilaian Kualitatif (asumsi 75) |
| 4 | Rentabilitas | 10% | ROA (Laba / Aset Produktif) |
| 5 | Likuiditas | 10% | Kas & Setara Kas / Liabilitas Lancar |

**Skor Komposit** = Σ (skor_aspek × bobot)

---

## Skoring per Rasio

### Rasio Solvabilitas = Total Aset / Total Liabilitas × 100%
| Rentang | Skor |
|---|---|
| ≥ 120% | 100 |
| 115% – <120% | 75 |
| 110% – <115% | 50 |
| 105% – <110% | 25 |
| < 105% | 0 |

### Rasio Ekuitas = Total Ekuitas / Modal Disetor × 100%
| Rentang | Skor |
|---|---|
| ≥ 100% | 100 |
| 90% – <100% | 75 |
| 75% – <90% | 50 |
| 60% – <75% | 25 |
| < 60% | 0 |

### NPL Neto = (Kurang Lancar + Diragukan + Macet) / Outstanding × 100%
| Rentang | Skor |
|---|---|
| ≤ 2% | 100 |
| >2% – ≤3,5% | 75 |
| >3,5% – ≤5% | 50 |
| >5% – ≤10% | 25 |
| > 10% | 0 |

### Coverage PPAP = Cadangan Terbentuk / PPAP Wajib Minimum × 100%
| Rentang | Skor |
|---|---|
| ≥ 100% | 100 |
| 80% – <100% | 75 |
| 60% – <80% | 50 |
| 40% – <60% | 25 |
| < 40% | 0 |

### ROA = (Pendapatan – Biaya) / Total Aset × 100%
| Rentang | Skor |
|---|---|
| ≥ 2,5% | 100 |
| 1,5% – <2,5% | 75 |
| 0,5% – <1,5% | 50 |
| 0% – <0,5% | 25 |
| < 0% | 0 |

### Rasio Likuiditas = Kas & Setara Kas / Liabilitas Lancar × 100%
| Rentang | Skor |
|---|---|
| ≥ 10% | 100 |
| 7% – <10% | 75 |
| 4% – <7% | 50 |
| 2% – <4% | 25 |
| < 2% | 0 |

### Manajemen (Kualitatif)
Asumsi skor tetap **75** (placeholder, belum integrasi form penilaian kualitatif).

---

## Peringkat Komposit (PK)

| PK | Label | Rentang Skor |
|---|---|---|
| 1 | Sangat Sehat | ≥ 81 |
| 2 | Sehat | 66 – <81 |
| 3 | Cukup Sehat | 51 – <66 |
| 4 | Kurang Sehat | <51 (kecuali trigger PK 5) |
| 5 | Tidak Sehat | **Trigger otomatis** |

### Trigger PK 5 (otomatis)
Wajib set PK=5 jika salah satu kondisi terpenuhi, **terlepas dari skor komposit**:
- NPL Neto ≥ 25%
- Rasio Ekuitas < 50%
- Coverage PPAP < 50%

---

## Status Pengawasan OJK

| Status | Trigger |
|---|---|
| **Pengawasan Normal** (hijau, `#28a745`) | PK ≤ 3, Rasio Ekuitas ≥ 75%, NPL Neto ≤ 5% |
| **Pengawasan Intensif** (oranye, `#fd7e14`) | PK = 4, atau Rasio Ekuitas 50%–<75%, atau NPL Neto >5%–<25% |
| **Pengawasan Khusus** (merah, `#dc3545`) | PK = 5, atau Rasio Ekuitas <50%, atau NPL Neto ≥25% |

View menampilkan alasan spesifik yang memicu status (misal "Peringkat Komposit (PK 4)" atau "Rasio Ekuitas 50% s.d <75% (60.50%)").

---

## Perhitungan PPAP per Kolektibilitas

| Kolek | Nama | % PPAP Wajib | Dampak ke NPL Neto |
|---|---|---|---|
| 1 | Lancar | 0% | Tidak |
| 2 | Dalam Perhatian Khusus (DPK) | 5% | Tidak |
| 3 | Kurang Lancar | 15% | **Ya** |
| 4 | Diragukan | 50% | **Ya** |
| 5 | Macet | 100% | **Ya** |

Logika di controller `PTK_POJK()`:
```php
foreach ($kolek_items as $idx => $item) {
    $nama = strtolower($item['nama']);
    $is_lancar = str_contains($nama, 'lancar') && !str_contains($nama, 'kurang');
    $is_kurang_lancar = str_contains($nama, 'kurang');
    $is_diragukan = str_contains($nama, 'ragu');
    $is_macet = str_contains($nama, 'macet');
    $is_dpk = str_contains($nama, 'dpk') || str_contains($nama, 'dalam perhatian khusus');

    if ($is_kurang_lancar || $is_diragukan || $is_macet) {
        $npl_neto += $saldo;
    }

    if ($is_dpk)         $ppap_wajib_minimum += $saldo * 0.05;
    elseif ($is_kurang_lancar) $ppap_wajib_minimum += $saldo * 0.15;
    elseif ($is_diragukan)     $ppap_wajib_minimum += $saldo * 0.50;
    elseif ($is_macet)         $ppap_wajib_minimum += $saldo * 1.00;
}
```

Deteksi koleksi menggunakan **substring matching case-insensitive** pada nama koleksi dari `$kec->kolek` (JSON config). Cocok untuk koleksi kustom dengan nama seperti "Dalam Perhatian Khusus", "dpk", "kurang Lancar", "Diragukan", "Macet".

---

## Rekomendasi Otomatis

Rekomendasi di-generate dinamis dari rasio yang **tidak memenuhi** threshold. Contoh:

| Kondisi | Rekomendasi |
|---|---|
| `rasio_solvabilitas < 110%` | "PERMODALAN: Rasio Solvabilitas X% di bawah batas minimum 110%. Segera tingkatkan modal disetor atau konversi SHU menjadi ekuitas..." |
| `rasio_ekuitas < 75%` | "PERMODALAN: Rasio Ekuitas X% di bawah minimum 75%. Tambah modal disetor dari pemilik..." |
| `rasio_npl_neto > 5%` | "KUALITAS ASET: NPL Neto X% melebihi batas 5%. Lakukan restrukturisasi pinjaman bermasalah..." |
| `ppap_coverage < 100%` | "KUALITAS ASET: Cadangan PPAP yang terbentuk baru X% dari PPAP wajib minimum..." |
| `rasio_likuiditas < 4%` | "LIKUIDITAS: Rasio Kas & Setara Kas terhadap Liabilitas Lancar hanya X%..." |
| `roa < 0.5%` | "RENTABILITAS: ROA tercapai X%. Optimalkan margin jasa pinjaman..." |
| Manajemen | Selalu ditambahkan: dokumentasi rapat, kebijakan risiko, pelatihan SDM |
| **Semua rasio memenuhi** | "PERTAHANKAN: Kinerja keuangan LKM saat ini telah memenuhi seluruh rasio POJK..." |

---

## Struktur View (penilaian_tingkat_kesehatan.blade.php)

View terdiri dari 6 section:

| Section | Konten |
|---|---|
| **Header** | Judul laporan, nama LKM, periode, sub-judul POJK |
| **A. Parameter Keuangan** | Tabel 9 parameter: Aset, Liabilitas, Kas, Modal Disetor, Ekuitas, Outstanding, NPL, Cadangan PPAP |
| **B. Analisis Rasio Kuantitatif** | Tabel 5 aspek × sub-rasio dengan bobot, hasil, batas POJK, skor, status |
| **C. Ringkasan PK & Status** | Tabel referensi PK 1–5 + baris hasil penilian saat ini (warna sesuai status) |
| **D. Status Pengawasan** | Tabel threshold + baris status saat ini (warna sesuai) |
| **E. Ringkasan Cadangan PPAP** | Per kolektibilitas: Sisa Pokok, % PPAP, PPAP Wajib, Proporsi Terbentuk, Selisih |
| **F. Rekomendasi** | Daftar rekomendasi otomatis yang applicable |

Bagian tanda tangan:
- Memakai `$kec->ttd->tanda_tangan_pelaporan` dengan placeholder `{tanggal}` di-replace `$tanggal_kondisi`.

---

## Variabel yang Dikirim ke View

```php
$data['analisis'] = [
    // Parameter Keuangan
    'total_aset', 'total_liabilitas', 'kas_setara_kas', 'liabilitas_lancar',
    'modal_disetor', 'total_ekuitas', 'outstanding_pinjaman', 'npl_neto',
    'cadangan_ppap_terbentuk', 'ppap_wajib_minimum',

    // Kolektibilitas
    'kolek_items', 'sum_kolek_total',

    // Laba
    'laba_bersih', 'pendapatan', 'biaya', 'roa',

    // Rasio
    'rasio_solvabilitas', 'rasio_ekuitas', 'rasio_npl_neto',
    'rasio_likuiditas', 'ppap_coverage',

    // Skor
    'skor_permodalan', 'skor_kualitas_aset', 'skor_manajemen',
    'skor_rentabilitas', 'skor_likuiditas', 'skor_komposit',

    // Peringkat & Status
    'pk', 'pk_label',
    'status_pengawasan', 'status_pengawasan_label',
    'status_pengawasan_warna', 'status_pengawasan_alasan',

    // Rekomendasi
    'rekomendasi', // array of strings
];

$data['laporan']  = 'Penilaian Tingkat Kesehatan POJK';
$data['sub_judul'] = '...';        // "Periode ..." atau "Tahun ..."
$data['tgl']      = '...';        // copy of sub_judul
```

View juga memakai variabel dari setup controller (`$kec`, `$dir`, `$nama_lembaga`, `$tanggal_kondisi`, `$tgl_kondisi`). **Pastikan** variabel-variabel ini tersedia atau extend layout `pelaporan.layout.base` menyediakannya.

---

## Kolektibilitas di View — Konsistensi dengan KBP2

View PTK_POJK menampilkan ringkasan PPAP per kolektibilitas dengan nama koleksi yang bersumber dari helper `tingkat_kesehatan()` → `$kec->kolek` (JSON config). Untuk konsistensi, view KBP2 (`kolekbilitas_pinjaman2.blade.php`) sudah diseragamkan memakai koleksi POJK tetap:

| Kolek | Nama | PPAP | Durasi |
|---|---|---|---|
| 1 | Lancar | 0% | <10 hari |
| 2 | Dalam Perhatian Khusus | 5% | <90 hari |
| 3 | Kurang Lancar | 15% | <120 hari |
| 4 | Diragukan | 50% | <180 hari |
| 5 | Macet | 100% | ≥180 hari |

Struktur ini identik dengan tabel PPAP di PTK_POJK, sehingga:
- Laporan PTK_POJK (agregat, ringkasan) → KBP2 (detail, per-pinjaman) saling konsisten
- Persentase PPAP dan threshold klasifikasi sama

---

## Batasan & Catatan

### ⚠️ Pinjaman Kelompok Belum Termasuk
Saat ini `Keuangan::tingkat_kesehatan()` hanya query `PinjamanIndividu`. **Pinjaman Kelompok** belum diagregat ke:
- Outstanding Pinjaman
- NPL Neto
- PPAP Wajib Minimum
- Skor Kualitas Aset
- Status Pengawasan

**Dampak**: rasio NPL/PPAP/Saldo understated, bisa membuat LKM terlihat lebih sehat dari真实 (terutama LKM dengan porsi besar pinjaman kelompok).

### ⚠️ Aspek Manajemen
Skor manajemen **hardcode 75** — belum ada form penilaian kualitatif yang feed nilai riil. Untuk produksi, perlu:
1. Buat tabel `penilaian_manajemen` per periode
2. Form input 5 sub-aspek: Risiko, Tata Kelola, Kepatuhan, Strategi, SDM
3. Modifikasi `PTK_POJK()` untuk baca skor dari tabel tersebut

### ⚠️ Naming Convention
Method `PTK_POJK` ditulis uppercase. Disarankan rename ke `ptk_pojk` (snake_case) agar konsisten dengan method pelaporan lain. Pastikan semua referensi UI/form di-update setelah rename.

### ⚠️ Magic Number Paper Size
`Session::get('lokasi') == 109` untuk paper size kustom — nilai hardcode, sebaiknya refactor ke konstanta atau env config.

---

## Riwayat Perubahan

| Versi | Perubahan |
|---|---|
| Initial | Penambahan method `PTK_POJK()` di controller + view `penilaian_tingkat_kesehatan.blade.php` |
| Konsistensi KBP2 | View `kolekbilitas_pinjaman2.blade.php` diseragamkan ke 5 koleksi POJK tetap |

---

## Referensi Regulasi

- **POJK tentang Penilaian Tingkat Kesehatan Lembaga Jasa Keuangan Mikro**
- **SEOJK No. 21/SEOJK.06/2015** — bobot aspek penilaian
- Parameter kolektibilitas: Lancar / DPK / Kurang Lancar / Diragukan / Macet dengan PPAP 0% / 5% / 15% / 50% / 100%
