<?php

/**
 * Helper global untuk sistem_angsuran, dipakai di luar konteks Laravel
 * (mis. public/generate.php & public/generate_individu.php yang procedural).
 */

if (! function_exists('sistem_angsuran_jenis')) {
    /**
     * Ambil nilai 'jenis' dari tabel sistem_angsuran via koneksi mysqli.
     *
     * @param  mysqli  $koneksi
     * @param  int|string  $id
     * @return string|null  'bulanan'|'harian'|'bulanan_ditunda'|null
     */
    function sistem_angsuran_jenis($koneksi, $id)
    {
        static $cache = [];
        $key = (string) $id;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $id_esc = mysqli_real_escape_string($koneksi, (string) $id);
        $row = mysqli_fetch_array(mysqli_query(
            $koneksi,
            "SELECT jenis FROM sistem_angsuran WHERE id='{$id_esc}' LIMIT 1"
        ));

        return $cache[$key] = $row ? ($row['jenis'] ?? null) : null;
    }
}

if (! function_exists('sistem_angsuran_is_harian')) {
    /**
     * Shortcut cek apakah id sistem_angsuran termasuk kategori 'harian'.
     */
    function sistem_angsuran_is_harian($koneksi, $id): bool
    {
        return sistem_angsuran_jenis($koneksi, $id) === 'harian';
    }
}

if (! function_exists('sistem_angsuran_is_bulanan_ditunda')) {
    /**
     * Shortcut cek apakah id sistem_angsuran termasuk kategori 'bulanan_ditunda'.
     */
    function sistem_angsuran_is_bulanan_ditunda($koneksi, $id): bool
    {
        return sistem_angsuran_jenis($koneksi, $id) === 'bulanan_ditunda';
    }
}

if (! function_exists('sistem_angsuran_field')) {
    /**
     * Ambil sembarang kolom dari sistem_angsuran via koneksi mysqli.
     *
     * @param  mysqli  $koneksi
     * @param  int|string  $id
     * @param  string  $field  mis. 'sistem', 'tunda_bulan', 'jenis'
     * @return mixed
     */
    function sistem_angsuran_field($koneksi, $id, string $field)
    {
        static $cache = [];
        $key = (string) $id . ':' . $field;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $allow = ['sistem', 'jenis', 'interval_hari', 'tunda_bulan', 'nama_sistem', 'deskripsi_sistem'];
        if (! in_array($field, $allow, true)) {
            return null;
        }

        $id_esc = mysqli_real_escape_string($koneksi, (string) $id);
        $row = mysqli_fetch_array(mysqli_query(
            $koneksi,
            "SELECT `{$field}` FROM sistem_angsuran WHERE id='{$id_esc}' LIMIT 1"
        ));

        return $cache[$key] = $row ? ($row[$field] ?? null) : null;
    }
}