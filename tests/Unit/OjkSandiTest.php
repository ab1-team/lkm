<?php

namespace Tests\Unit;

use App\Support\Ojk\OjkSandi;
use Tests\TestCase;

/**
 * Aturan sandi OJK untuk Formulir 05.02 kolom g (Periode Pembayaran) dan
 * kolom m (Kualitas / Kolektibilitas).
 *
 * Semua test memakai array/calar murni — tidak menyentuh database.
 */
class OjkSandiTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Kolom g — Periode Pembayaran: 4 kondisi dari brief
    |--------------------------------------------------------------------------
    */

    public function test_kolom_g_harian_dengan_sistem_satu_menghasilkan_harian(): void
    {
        // Kondisi 1: jenis='harian' DAN sistem=1 -> 1
        $this->assertSame(1, OjkSandi::periodePembayaran('harian', 1));
    }

    public function test_kolom_g_harian_dengan_sistem_lain_menghasilkan_mingguan(): void
    {
        // Kondisi 2: jenis='harian' saja -> 2
        $this->assertSame(2, OjkSandi::periodePembayaran('harian', 2));
        $this->assertSame(2, OjkSandi::periodePembayaran('harian', 7));
        $this->assertSame(2, OjkSandi::periodePembayaran('harian', 14));
    }

    public function test_kolom_g_bulanan_dengan_sistem_dua_belas_menghasilkan_tahunan(): void
    {
        // Kondisi 3: jenis='bulanan' DAN sistem=12 -> 6
        $this->assertSame(6, OjkSandi::periodePembayaran('bulanan', 12));
    }

    public function test_kolom_g_selain_itunya_menghasilkan_bulanan(): void
    {
        // Kondisi 4: selain itu -> 3
        $this->assertSame(3, OjkSandi::periodePembayaran('bulanan', 1));
        $this->assertSame(3, OjkSandi::periodePembayaran('bulanan', 3));
        $this->assertSame(3, OjkSandi::periodePembayaran('bulanan', 24));
    }

    public function test_kolom_g_bulanan_ditunda_diperlakukan_sebagai_bulanan(): void
    {
        // 'bulanan_ditunda' bukan 'bulanan'/'harian', jadi jatuh ke kondisi 4.
        $this->assertSame(3, OjkSandi::periodePembayaran('bulanan_ditunda', 1));
        $this->assertSame(3, OjkSandi::periodePembayaran('bulanan_ditunda', 12));
    }

    public function test_kolom_g_urutan_kondisi_berhenti_di_kondisi_pertama_yang_cocok(): void
    {
        // 'harian' + sistem=12 harus ikut kondisi 1/2 (harian), bukan
        // kondisi 3 (tahunan) yang menuntut jenis='bulanan'.
        $this->assertSame(2, OjkSandi::periodePembayaran('harian', 12));
    }

    public function test_kolom_g_tidak_pernah_menghasilkan_sandi_4_atau_5(): void
    {
        // Brief: kode 4 (Selapanan) dan 5 (Musiman) tidak pernah dihasilkan.
        $kombinasi = [
            ['harian', 1], ['harian', 2], ['harian', 3], ['harian', 12],
            ['bulanan', 1], ['bulanan', 2], ['bulanan', 3], ['bulanan', 12],
            ['bulanan', 24], ['bulanan', 36],
            ['bulanan_ditunda', 1], ['bulanan_ditunda', 12],
            ['', 1], [null, null],
        ];

        foreach ($kombinasi as [$jenis, $sistem]) {
            $kode = OjkSandi::periodePembayaran($jenis, $sistem);
            $this->assertNotSame(4, $kode, "sandi 4 tidak boleh dihasilkan ({$jenis}/{$sistem})");
            $this->assertNotSame(5, $kode, "sandi 5 tidak boleh dihasilkan ({$jenis}/{$sistem})");
        }
    }

    public function test_label_kolom_g_terpetakan(): void
    {
        $this->assertSame('Harian', OjkSandi::labelPeriodePembayaran(1));
        $this->assertSame('Mingguan', OjkSandi::labelPeriodePembayaran(2));
        $this->assertSame('Bulanan', OjkSandi::labelPeriodePembayaran(3));
        $this->assertSame('Tahunan', OjkSandi::labelPeriodePembayaran(6));
        $this->assertNull(OjkSandi::labelPeriodePembayaran(99));
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom m — ambang kolektibilitas (DPD, tabel 41/2024)
    |--------------------------------------------------------------------------
    */

    public function test_ambang_kolektibilitas_batas_kategori(): void
    {
        // DPD 0..9 -> Lancar
        $this->assertSame(0, OjkSandi::kategoriKolektibilitas(0));
        $this->assertSame(0, OjkSandi::kategoriKolektibilitas(9));

        // DPD 10..89 -> Dalam Perhatian Khusus
        $this->assertSame(1, OjkSandi::kategoriKolektibilitas(10));
        $this->assertSame(1, OjkSandi::kategoriKolektibilitas(89));

        // DPD 90..119 -> Kurang Lancar
        $this->assertSame(2, OjkSandi::kategoriKolektibilitas(90));
        $this->assertSame(2, OjkSandi::kategoriKolektibilitas(119));

        // DPD 120..179 -> Diragukan
        $this->assertSame(3, OjkSandi::kategoriKolektibilitas(120));
        $this->assertSame(3, OjkSandi::kategoriKolektibilitas(179));

        // DPD >= 180 -> Macet
        $this->assertSame(4, OjkSandi::kategoriKolektibilitas(180));
        $this->assertSame(4, OjkSandi::kategoriKolektibilitas(3650));
    }

    public function test_kolektibilitas_tidak_tidak_langsung(): void
    {
        // Ambang bersifat "< batas", jadi tepat di batas atas sudah pindah
        // kategori. Ini yang paling mudah salah satu digit.
        $this->assertSame(0, OjkSandi::kategoriKolektibilitas(9));
        $this->assertSame(1, OjkSandi::kategoriKolektibilitas(10));
        $this->assertSame(1, OjkSandi::kategoriKolektibilitas(89));
        $this->assertSame(2, OjkSandi::kategoriKolektibilitas(90));
        $this->assertSame(3, OjkSandi::kategoriKolektibilitas(120));
        $this->assertSame(4, OjkSandi::kategoriKolektibilitas(180));
    }

    public function test_dpd_negatif_dianggap_lancar(): void
    {
        // Jatuh tempo masih di masa depan = belum menunggak.
        $this->assertSame(0, OjkSandi::kategoriKolektibilitas(-5));
        $this->assertSame('Lancar', OjkSandi::labelKolektibilitas(-5));
    }

    public function test_label_kolektibilitas(): void
    {
        $this->assertSame('Lancar', OjkSandi::labelKolektibilitas(0));
        $this->assertSame('Dalam Perhatian Khusus', OjkSandi::labelKolektibilitas(10));
        $this->assertSame('Kurang Lancar', OjkSandi::labelKolektibilitas(90));
        $this->assertSame('Diragukan', OjkSandi::labelKolektibilitas(120));
        $this->assertSame('Macet', OjkSandi::labelKolektibilitas(180));
    }

    public function test_kolektibilitas_naik_monoton(): void
    {
        $sebelumnya = -1;
        for ($dpd = 0; $dpd <= 400; $dpd += 7) {
            $kategori = OjkSandi::kategoriKolektibilitas($dpd);
            $this->assertGreaterThanOrEqual(
                $sebelumnya,
                $kategori,
                'kolektibilitas harus tidak menurun saat DPD bertambah'
            );
            $sebelumnya = $kategori;
        }
    }

    public function test_sandi_kolektibilitas_null_sampai_tabel_sandi_ojk_diisi(): void
    {
        // config('ojk.kolektibilitas_sandi') sengaja kosong: tidak ada kode
        // sandi OJK yang terverifikasi, jadi mapper tidak boleh mengarang.
        foreach ([0, 10, 90, 120, 180] as $dpd) {
            $this->assertNull(OjkSandi::sandiKolektibilitas($dpd));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom c — jenis nasabah
    |--------------------------------------------------------------------------
    */

    public function test_kolom_c_individu_dan_kelompok(): void
    {
        $this->assertSame(1, OjkSandi::jenisNasabah(true));
        $this->assertSame(2, OjkSandi::jenisNasabah(false));
        $this->assertSame('Individu', OjkSandi::labelJenisNasabah(1));
        $this->assertSame('Kelompok', OjkSandi::labelJenisNasabah(2));
        $this->assertNull(OjkSandi::labelJenisNasabah(3));
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom e / f / n — harus reporting gap, bukan mengarang
    |--------------------------------------------------------------------------
    */

    public function test_kolom_e_dan_f_mengembalikan_null_sampai_tabel_sandi_ada(): void
    {
        // Nilai 4 dan 7 yang tersimpan di jenis_produk_pinjaman tidak boleh
        // dipetakan ke sandi apa pun sebelum tabel sandi OJK diberikan.
        $this->assertNull(OjkSandi::jenisPenggunaan(4));
        $this->assertNull(OjkSandi::sektorUsaha(7));
        $this->assertNull(OjkSandi::jenisPenggunaan(null));
        $this->assertNull(OjkSandi::sektorUsaha(0));
    }

    public function test_kolom_n_mengembalikan_null_sampai_pemetaan_agunan_diisi(): void
    {
        $this->assertNull(OjkSandi::jenisAgunan(1));
        $this->assertNull(OjkSandi::jenisAgunan(5));
        $this->assertNull(OjkSandi::jenisAgunan(null));
        $this->assertNull(OjkSandi::jenisAgunan(99));
    }

    public function test_nilai_agunan_dibaca_per_kode_jaminan(): void
    {
        $tanah = '{"jenis_jaminan":"1","nilai_jual_tanah":"300000000"}';
        $kendaraan = '{"jenis_jaminan":"2","nilai_jual_kendaraan":"10000000"}';
        $lain = '{"jenis_jaminan":"4","nilai_jaminan":2500000}';

        $this->assertSame(300000000.0, OjkSandi::nilaiAgunan($tanah));
        $this->assertSame(10000000.0, OjkSandi::nilaiAgunan($kendaraan));
        $this->assertSame(2500000.0, OjkSandi::nilaiAgunan($lain));
    }

    public function test_nilai_agunan_kosong_kode_tiga_tidak_menghasilkan_nol_palsu(): void
    {
        // SK Pegawai (kode 3) memang tidak punya nilai di JSON. Hasilnya
        // harus null supaya baris masuk Data Gap, bukan 0 yang terlihat sah.
        $this->assertNull(OjkSandi::nilaiAgunan('{"jenis_jaminan":"3"}'));
        $this->assertNull(OjkSandi::nilaiAgunan(null));
        $this->assertNull(OjkSandi::nilaiAgunan('bukan json'));
        $this->assertNull(OjkSandi::nilaiAgunan('{}'));
    }

    public function test_nilai_agunan_mengembalikan_float_bukan_string(): void
    {
        // number_format() menolak string — nilai harus numeric.
        $hasil = OjkSandi::nilaiAgunan('{"jenis_jaminan":"1","nilai_jual_tanah":"1000000.50"}');

        $this->assertIsFloat($hasil);
        $this->assertSame(1000000.50, $hasil);
        $this->assertSame('1,000,000.50', number_format($hasil, 2));
    }
}
