<?php

namespace Tests\Unit;

use App\Support\Ojk\DrpPinjamanDiberikan;
use App\Support\Ojk\OjkSandi;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Perhitungan baki debet (kolom k), tunggakan (kolom l), dan DPD (kolom m)
 * pada DrpPinjamanDiberikan.
 *
 * Test memakai database pengembangan yang sudah ada — LKM 109 (Klirong)
 * punya data pinjaman nyata. Tidak ada factory/fixture di repo ini,
 * sehingga test ini TIDAK membuat atau mengubah data.
 */
class DrpBakiDebetTest extends TestCase
{
    private const LOKASI = 109;

    private function loanId(): ?string
    {
        $id = DB::table('pinjaman_anggota_'.self::LOKASI)
            ->where('status', 'A')
            ->where('jenis_pinjaman', 'I')
            ->whereNotNull('tgl_cair')
            ->whereNotNull('alokasi')
            ->where('alokasi', '>', 0)
            ->orderBy('tgl_cair')
            ->value('id');

        return $id === null ? null : (string) $id;
    }

    private function baris(string $loanId, string $tglLaporan): ?array
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build($tglLaporan);

        return $hasil['rows']->firstWhere('loan_id', $loanId);
    }

    private function testLKMAda(): void
    {
        foreach (['pinjaman_anggota_', 'anggota_', 'rencana_angsuran_i_', 'real_angsuran_i_'] as $prefix) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasTable($prefix.self::LOKASI),
                "tabel {$prefix}".self::LOKASI.' harus ada'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom k — Saldo Pinjaman (Baki Debet)
    |--------------------------------------------------------------------------
    */

    public function test_baki_debet_sama_dengan_saldo_pokok_transaksi_terakhir(): void
    {
        $loanId = $this->loanId();
        if ($loanId === null) {
            $this->markTestSkipped('tidak ada pinjaman aktif di LKM '.self::LOKASI);
        }

        $tgl = '2026-12-31';
        $baris = $this->baris($loanId, $tgl);
        $this->assertNotNull($baris, "pinjaman $loanId harus muncul di laporan");

        // Saldo yang diharapkan: baris real_angsuran terakhir s/d tanggal.
        $saldo = DB::table('real_angsuran_i_'.self::LOKASI)
            ->where('loan_id', $loanId)
            ->where('tgl_transaksi', '<=', $tgl)
            ->orderBy('tgl_transaksi', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($saldo);
        $this->assertSame(
            round((float) $saldo->saldo_pokok, 2),
            round($baris['baki_debet'], 2),
            'kolom k harus sama dengan saldo_pokok transaksi terakhir'
        );
    }

    public function test_baki_debet_tidak_pernah_lebih_besar_daripada_nilai_pencairan(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['rows'] as $row) {
            $this->assertLessThanOrEqual(
                $row['nilai_pencairan'] + 0.01,
                $row['baki_debet'],
                "pinjaman {$row['loan_id']}: baki debet melebihi nilai pencairan"
            );
            $this->assertGreaterThanOrEqual(0, $row['baki_debet'], 'baki debet negatif');
        }
    }

    public function test_pinjaman_lunas_sebelum_tanggal_laporan_dikeluarkan(): void
    {
        // Loans status L/R/H hanya masuk laporan kalau tgl_lunas masih
        // SETELAH tanggal laporan (masih aktif saat itu). Yang sudah lunas
        // sebelumnya tidak boleh muncul sama sekali.
        $loanId = DB::table('pinjaman_anggota_'.self::LOKASI)
            ->whereIn('status', ['L', 'R', 'H'])
            ->whereNotNull('tgl_lunas')
            ->where('tgl_lunas', '<=', '2026-12-31')
            ->value('id');

        if ($loanId === null) {
            $this->markTestSkipped('tidak ada pinjaman lunas di LKM '.self::LOKASI);
        }

        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        $this->assertNull(
            $hasil['rows']->firstWhere('loan_id', (string) $loanId),
            'pinjaman yang sudah lunas sebelum tanggal laporan tidak boleh masuk daftar'
        );
    }

    public function test_pinjaman_lunas_dimudian_hari_masih_masuk_dengan_nol(): void
    {
        // Tengkurik: status L dengan tgl_lunas setelah tanggal laporan masih
        // aktif, tapi baki debet / tunggakan / DPD-nya sudah nol.
        $row = DB::table('pinjaman_anggota_'.self::LOKASI)
            ->whereIn('status', ['L', 'R', 'H'])
            ->whereNotNull('tgl_lunas')
            ->where('tgl_lunas', '>', '2026-12-31')
            ->orderBy('tgl_cair')
            ->first(['id', 'tgl_lunas']);

        if ($row === null) {
            $this->markTestSkipped('tidak ada pinjaman lunas setelah tanggal laporan');
        }

        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');
        $baris = $hasil['rows']->firstWhere('loan_id', (string) $row->id);

        $this->assertNotNull($baris, 'pinjaman lunas setelah tanggal laporan masih aktif');
        $this->assertSame(0.0, round($baris['baki_debet'], 2));
        $this->assertSame(0.0, round($baris['tunggakan'], 2));
        $this->assertSame(0, (int) $baris['dpd']);
        $this->assertSame('Lancar', $baris['kolektibilitas_label']);
    }

    public function test_baki_debet_bukan_sekadar_alokasi_k_when_ada_pembayaran(): void
    {
        // Menangkap regresi: implementasi lama pernah mengembalikan
        // alokasi utuh tanpa membaca real_angsuran.
        $loanId = DB::table('pinjaman_anggota_'.self::LOKASI)
            ->where('status', 'A')
            ->where('jenis_pinjaman', 'I')
            ->where('tgl_cair', '<=', '2026-12-31')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('real_angsuran_i_'.self::LOKASI)
                    ->whereColumn('real_angsuran_i_'.self::LOKASI.'.loan_id', 'pinjaman_anggota_'.self::LOKASI.'.id')
                    ->where('saldo_pokok', '>', 0)
                    ->where('saldo_pokok', '<', DB::raw('pinjaman_anggota_'.self::LOKASI.'.alokasi'));
            })
            ->value('id');

        if ($loanId === null) {
            $this->markTestSkipped('tidak ada pinjaman dengan pembayaran sebagian');
        }

        $baris = $this->baris((string) $loanId, '2026-12-31');
        $this->assertNotNull($baris);
        $this->assertLessThan(
            $baris['nilai_pencairan'],
            $baris['baki_debet'],
            'baki debet seharusnya lebih kecil dari nilai pencairan setelah ada cicilan'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom l — Tunggakan
    |--------------------------------------------------------------------------
    */

    public function test_tunggakan_menggunakan_target_kumulatif_bukan_jumlah_seluruh_angsuran(): void
    {
        // Regresi kritis: menjumlahkan target_pokok SEMUA angsuran yang lewat
        // menghitung pokok berkali-kali. Untuk pinjaman 50 juta hasilnya
        // bisa jauh melebihi nilai pinjaman.
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['rows'] as $row) {
            $this->assertLessThanOrEqual(
                $row['nilai_pencairan'] + 0.01,
                $row['tunggakan'],
                "pinjaman {$row['loan_id']}: tunggakan melebihi nilai pencairan"
            );
        }
    }

    public function test_tunggakan_tidak_negatif(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['rows'] as $row) {
            $this->assertGreaterThanOrEqual(0, $row['tunggakan'], 'tunggakan negatif');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom m — DPD & kolektibilitas
    |--------------------------------------------------------------------------
    */

    public function test_dpd_konsisten_dengan_kategori_kolektibilitas(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['rows'] as $row) {
            $this->assertSame(
                OjkSandi::labelKolektibilitas((int) $row['dpd']),
                $row['kolektibilitas_label'],
                "pinjaman {$row['loan_id']}: DPD dan label kolektibilitas tidak sinkron"
            );
            $this->assertGreaterThanOrEqual(0, $row['dpd'], 'DPD negatif');
        }
    }

    public function test_baris_dengan_dpd_nol_termasuk_lancar(): void
    {
        // Data LKM 109 tidak punya pinjaman yang DPD 0 (min DPD di sana 22),
        // jadi test ini hanya memverifikasi konsistensi kalau baris DPD 0
        // benar-benar ada — bukan memaksa dataset punya baris seperti itu.
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        $adaDpdNol = false;
        foreach ($hasil['rows'] as $row) {
            if ((int) $row['dpd'] !== 0) {
                continue;
            }

            $adaDpdNol = true;
            $this->assertSame(
                'Lancar',
                $row['kolektibilitas_label'],
                "pinjaman {$row['loan_id']}: DPD 0 harus Lancar"
            );
            $this->assertSame(0.0, round($row['tunggakan'], 2));
        }

        // Tetapkan invarian yang selalu benar walau tidak ada baris DPD 0:
        // DPD tidak boleh negatif dan label kolektibilitas harus salah
        // satu dari lima kategori resmi.
        foreach ($hasil['rows'] as $row) {
            $this->assertGreaterThanOrEqual(0, (int) $row['dpd']);
            $this->assertContains($row['kolektibilitas_label'], [
                'Lancar',
                'Dalam Perhatian Khusus',
                'Kurang Lancar',
                'Diragukan',
                'Macet',
            ]);
        }

        if (! $adaDpdNol) {
            $this->markTestSkipped('LKM '.self::LOKASI.' tidak punya pinjaman DPD 0');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Data Gap Warning
    |--------------------------------------------------------------------------
    */

    public function test_data_gap_mencatat_seluruh_kolom_yang_belum_terisi(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');
        $gap = $hasil['gap'];

        $this->assertSame($hasil['rows']->count(), $gap['total']);

        // Kolom c, d, h, i, j, k, l selalu bisa dihitung, jadi tidak boleh
        // muncul sebagai gap.
        foreach ($gap['per_kolom'] as $kolom => $jumlah) {
            $this->assertNotContains($kolom, [
                'c_jenis_nasabah',
                'd_nomor_identitas',
                'h_jangka_waktu',
                'i_suku_bunga',
                'j_nilai_pencairan',
                'k_baki_debet',
                'l_tunggakan',
            ], "kolom $kolom seharusnya tidak pernah jadi gap");
        }
    }

    public function test_baris_gap_memuat_alasan_yang_bisa_dibaca(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['gap']['baris'] as $b) {
            $this->assertNotEmpty($b['alasan']);
            $this->assertSame(count($b['kolom']), count($b['alasan']));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Struktur baris a-o
    |--------------------------------------------------------------------------
    */

    public function test_baris_memiliki_kolom_a_sampai_o(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');
        $baris = $hasil['rows']->first();

        if ($baris === null) {
            $this->markTestSkipped('tidak ada baris untuk diperiksa');
        }

        foreach ([
            'no', 'nama', 'nama_lengkap', 'loan_id', 'jenis_nasabah', 'nomor_identitas',
            'jenis_penggunaan', 'sektor_usaha', 'periode_pembayaran',
            'tgl_mulai', 'tgl_jatuh_tempo', 'suku_bunga',
            'nilai_pencairan', 'baki_debet', 'tunggakan',
            'kolektibilitas', 'jenis_agunan', 'nilai_agunan',
        ] as $kolom) {
            $this->assertArrayHasKey($kolom, $baris, "kolom $kolom wajib ada");
        }
    }

    public function test_nomor_urut_mulai_dari_satu_dan_kontinu(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');
        $rows = $hasil['rows'];

        $this->assertSame($rows->count(), count($rows->pluck('no')->all()));

        foreach ($rows as $index => $row) {
            $this->assertSame(
                $index + 1,
                $row['no'],
                'kolom a harus nomor urut 1..n berurutan'
            );
        }
    }

    public function test_nama_nasabah_dicetak_kapital(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['rows'] as $row) {
            $this->assertSame(
                mb_strtoupper($row['nama']),
                $row['nama'],
                'kolom b harus huruf kapital'
            );

            // Kolom b = "NAMA - ID".
            $this->assertSame(
                $row['nama'].' - '.$row['loan_id'],
                $row['nama_lengkap'],
                'kolom b harus NAMA - ID'
            );
            $this->assertNotSame('', $row['loan_id']);
        }
    }

    public function test_nomor_identitas_selalu_string_bukan_angka(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        foreach ($hasil['rows'] as $row) {
            $this->assertIsString(
                $row['nomor_identitas'],
                "kolom d pinjaman {$row['loan_id']} harus string"
            );
        }
    }

    public function test_urutan_flat_mengikuti_tanggal_pencairan(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');
        $tgl = $hasil['rows']->pluck('tgl_mulai')->all();

        $terurut = $tgl;
        sort($terurut);

        $this->assertSame($terurut, $tgl, 'baris harus urut tgl_cair ascending tanpa pengelompokan');
    }

    public function test_lokasi_tanpa_tabel_pinjaman_menghasilkan_daftar_kosong(): void
    {
        // LKM 234 (Wonosari) tidak punya tabel pinjaman. Harus kosong,
        // bukan exception.
        $hasil = (new DrpPinjamanDiberikan(234))->build('2026-12-31');

        $this->assertCount(0, $hasil['rows']);
        $this->assertSame(0, $hasil['gap']['total']);
    }

    public function test_total_sejajar_dengan_jumlah_baris(): void
    {
        $hasil = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        $this->assertSame($hasil['rows']->count(), $hasil['total']['jumlah_baris']);
        $this->assertEqualsWithDelta(
            $hasil['rows']->sum('baki_debet'),
            $hasil['total']['baki_debet'],
            0.01
        );
        $this->assertEqualsWithDelta(
            $hasil['rows']->sum('tunggakan'),
            $hasil['total']['tunggakan'],
            0.01
        );
    }

    public function test_tanggal_laporan_mengubah_hasil_khitungan(): void
    {
        $awal = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-01-31');
        $akhir = (new DrpPinjamanDiberikan(self::LOKASI))->build('2026-12-31');

        // Pinjaman yang dicairkan setelah tanggal laporan awal tidak boleh
        // bocor ke laporan awal — inilah yang diuji di sini.
        $tglAwal = '2026-01-31';
        $dicairkanSetelah = DB::table('pinjaman_anggota_'.self::LOKASI)
            ->where('status', 'A')
            ->where('jenis_pinjaman', 'I')
            ->where('tgl_cair', '>', $tglAwal)
            ->where('tgl_cair', '<=', '2026-12-31')
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->all();

        if ($dicairkanSetelah === []) {
            $this->markTestSkipped('tidak ada pinjaman yang dicairkan di tengah tahun');
        }

        $idAwal = $awal['rows']->pluck('loan_id')->all();

        $this->assertEmpty(
            array_intersect($dicairkanSetelah, $idAwal),
            'laporan per '.$tglAwal.' tidak boleh memuat pinjaman yang dicairkan setelah tanggal itu'
        );

        // Sebaliknya: semua baris laporan awal HARUS ada di laporan akhir,
        // kecuali yang benar-benar sudah lunas di antara dua tanggal.
        $idAkhir = $akhir['rows']->pluck('loan_id')->all();
        $sudahLunas = DB::table('pinjaman_anggota_'.self::LOKASI)
            ->whereIn('id', array_map('intval', $awal['rows']->pluck('loan_id')->all()))
            ->whereNotNull('tgl_lunas')
            ->where('tgl_lunas', '<=', '2026-12-31')
            ->where('tgl_lunas', '>', $tglAwal)
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->all();

        $this->assertEmpty(
            array_diff(array_diff($idAwal, $idAkhir), $sudahLunas),
            'hanya pinjaman yang benar-benar lunas boleh hilang dari laporan akhir tahun'
        );

        // Tunggakan kumulatif tidak boleh turun seiring waktu bertambah.
        $this->assertGreaterThanOrEqual(
            $awal['total']['tunggakan'],
            $akhir['total']['tunggakan'] - 0.01,
            'total tunggakan tidak realistis berkurang seiring waktu'
        );
    }
}
