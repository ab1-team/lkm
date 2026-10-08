<?php

namespace App\Support\Ojk;

use InvalidArgumentException;

/**
 * Pemetaan sandi OJK untuk Laporan "Daftar Rincian Pinjaman yang Diberikan"
 * (SEOJK No. 1/SEOJK.06/2025 — Formulir 05.02).
 *
 * Kolom laporan yang berupa kode: c, e, f, g, m, n.
 *
 * Prinsip: kelas ini HANYA menerjemahkan nilai database menjadi sandi yang
 * terdefinisi di config('ojk'). Nilai yang tidak ada di tabel sandi
 * dikembalikan sebagai gap (lihat isGap/label), bukan diterjemahkan ke
 * angka atau label closest-match. Dengan begitu pemanggil bisa menandai
 * baris sebagai Data Gap Warning alih-alih deceived oleh tebakan.
 *
 * Semua metode murni (tidak menyentuh database) supaya bisa diuji unit
 * tanpa fixture.
 */
final class OjkSandi
{
    /*
    |--------------------------------------------------------------------------
    | Kolom g — Periode Pembayaran
    |--------------------------------------------------------------------------
    | Aturan berurutan dari brief, berhenti di kondisi pertama yang cocok:
    |   1. jenis = 'harian' AND sistem = 1  -> 1
    |   2. jenis = 'harian'                -> 2
    |   3. jenis = 'bulanan' AND sistem = 12 -> 6
    |   4. selain itu                       -> 3
    |
    | Konsekuensi yang disengaja: kode 4 (Selapanan) dan 5 (Musiman)
    | tidak pernah dihasilkan.
    */
    public const PERIODE_HARIAN = 1;

    public const PERIODE_MINGGUAN = 2;

    public const PERIODE_BULANAN = 3;

    public const PERIODE_TAHUNAN = 6;

    /**
     * Terjemahkan sistem angsuran menjadi sandi kolom g.
     *
     * @param  string|null  $jenis   sistem_angsuran.jenis  ('harian'|'bulanan'|'bulanan_ditunda')
     * @param  int|null  $sistem  sistem_angsuran.sistem
     */
    public static function periodePembayaran(?string $jenis, $sistem): int
    {
        $sistem = (int) $sistem;

        if ($jenis === 'harian') {
            return $sistem === 1
                ? self::PERIODE_HARIAN
                : self::PERIODE_MINGGUAN;
        }

        if ($jenis === 'bulanan' && $sistem === 12) {
            return self::PERIODE_TAHUNAN;
        }

        return self::PERIODE_BULANAN;
    }

    /**
     * Label kolom g, atau null bila kode tidak dikenal.
     */
    public static function labelPeriodePembayaran(int $kode): ?string
    {
        return self::lookup('periode_pembayaran', $kode);
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom c — Jenis Nasabah
    |--------------------------------------------------------------------------
    */

    /**
     * DRP ini hanya memuat pinjaman individu (jenis_pinjaman = 'I').
     * Menyedekalkan kode 2 tetap disediakan agar kelas ini bisa dipakai
     * laporan lain tanpa diubah.
     */
    public static function jenisNasabah(bool $individu): int
    {
        return $individu ? 1 : 2;
    }

    public static function labelJenisNasabah(int $kode): ?string
    {
        return self::lookup('jenis_nasabah', $kode);
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom e & f — Jenis Penggunaan & Sektor Usaha
    |--------------------------------------------------------------------------
    | Nilai mentahnya datang dari jenis_produk_pinjaman.jenis / .usaha.
    | Kedua kolom itu tinyint NOT NULL di database, jadi 0 berarti belum
    | diisi oleh user — bukan kode yang sah.
    */

    public static function jenisPenggunaan($kode): ?int
    {
        return self::kodeDariTabel('jenis_penggunaan', $kode);
    }

    public static function sektorUsaha($kode): ?int
    {
        return self::kodeDariTabel('sektor_usaha', $kode);
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom m — Kualitas / Kolektibilitas
    |--------------------------------------------------------------------------
    */

    /**
     * Kategori kolektibilitas dari DPD (hari).
     *
     * Mengembalikan indeks 0..4 sesuai urutan config('ojk.kolektibilitas_ambang.nama').
     * DPD negatif (jatuh tempo masih di masa depan) dianggap Lancar.
     */
    public static function kategoriKolektibilitas(int $dpd): int
    {
        $dpd = max(0, $dpd);

        foreach (self::ambang() as $index => $batasAtas) {
            if ($dpd < $batasAtas) {
                return $index;
            }
        }

        return count(self::ambang()) - 1;
    }

    /**
     * Nama kategori kolektibilitas (Lancar / DPK / ... / Macet).
     */
    public static function labelKolektibilitas(int $dpd): string
    {
        $nama = (array) config('ojk.kolektibilitas_ambang.nama', []);

        return $nama[self::kategoriKolektibilitas($dpd)] ?? 'Tidak Diketahui';
    }

    /**
     * Sandi kolom m. Mengembalikan null selama tabel sandi OJK belum diisi —
     * lihat config('ojk.kolektibilitas_sandi').
     */
    public static function sandiKolektibilitas(int $dpd): ?int
    {
        $sandi = (array) config('ojk.kolektibilitas_sandi', []);
        $index = self::kategoriKolektibilitas($dpd);

        return isset($sandi[$index]) ? (int) $sandi[$index] : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Kolom n — Jenis Agunan / Penjaminan Kredit
    |--------------------------------------------------------------------------
    */

    /**
     * Petakan kode agunan internal (jaminan.jenis_jaminan) ke sandi OJK.
     *
     * Tabel pemetaan masih kosong, jadi selama ini selalu null. Laporan
     * menampilkan kode agunan apa adanya; fungsi ini dipakai hanya bila
     * tabel sandi someday diisi.
     */
    public static function jenisAgunan($kodeInternal): ?int
    {
        return self::kodeDariTabel('agunan', $kodeInternal);
    }

    /**
     * Nilai agunan dari JSON `jaminan`.
     *
     * Kunci nilai yang dipakai sistem (lihat also PelaporanController):
     *   1 tanah      -> nilai_jual_tanah
     *   2 kendaraan  -> nilai_jual_kendaraan
     *   4 lain-lain  -> nilai_jaminan
     *
     * Kode 3 (SK Pegawai) memang tidak punya nilai di JSON — itu gap data,
     * bukan alasan untuk mengembalikan angka 0 yang terlihat sah.
     */
    public static function nilaiAgunan(?string $jaminanJson): ?float
    {
        $jaminan = json_decode((string) $jaminanJson, true);

        if (! is_array($jaminan)) {
            return null;
        }

        $kunci = [
            '1' => 'nilai_jual_tanah',
            '2' => 'nilai_jual_kendaraan',
            '4' => 'nilai_jaminan',
        ];

        $kode = (string) ($jaminan['jenis_jaminan'] ?? '');
        $kunciDipakai = $kunci[$kode] ?? null;

        // Beberapa baris lama menaruh nilai pada kunci "nilai_jual_*" lain;
        // pakai yang pertama agar tidak mengarang.
        if ($kunciDipakai === null) {
            foreach ($jaminan as $key => $value) {
                if (str_starts_with((string) $key, 'nilai_jual_') && is_numeric($value)) {
                    $kunciDipakai = $key;
                    break;
                }
            }
        }

        if ($kunciDipakai === null || ! isset($jaminan[$kunciDipakai])) {
            return null;
        }

        return is_numeric($jaminan[$kunciDipakai])
            ? (float) $jaminan[$kunciDipakai]
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil kode sandi hanya jika nilainya benar-benar terdefinisi.
     *
     * Mengembalikan null untuk: null, 0 (belum diisi), nilai kosong, dan
     * kode yang tidak ada di tabel sandi.
     */
    private static function kodeDariTabel(string $tabel, $kode): ?int
    {
        if ($kode === null || $kode === '' || (int) $kode === 0) {
            return null;
        }

        $daftar = (array) config('ojk.'.$tabel, []);

        // Tabel sandi kosong = belum ada pemetaan, semua nilai adalah gap.
        if ($daftar === []) {
            return null;
        }

        $kode = (int) $kode;

        return array_key_exists($kode, $daftar) && $daftar[$kode] !== null
            ? $kode
            : null;
    }

    /**
     * Label dari tabel sandi, atau null bila tidak dikenal.
     */
    private static function lookup(string $tabel, int $kode): ?string
    {
        $daftar = (array) config('ojk.'.$tabel, []);

        return isset($daftar[$kode]) ? (string) $daftar[$kode] : null;
    }

    /**
     * @return array<int, int>
     */
    private static function ambang(): array
    {
        $ambang = config('ojk.kolektibilitas_ambang.hari', []);

        if (! is_array($ambang) || $ambang === []) {
            throw new InvalidArgumentException('config ojk.kolektibilitas_ambang.hari kosong');
        }

        return array_map('intval', $ambang);
    }
}
