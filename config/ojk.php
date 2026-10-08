<?php

/**
 * Tabel sandi untuk Laporan "Daftar Rincian Pinjaman yang Diberikan"
 * (SEOJK No. 1/SEOJK.06/2025 — Formulir 05.02).
 *
 * Aturan penting: TIDAK ADA nilai yang boleh dikarang di file ini.
 * Setiap kategori bertanda `sandi => null` berarti "kode OJK-nya belum
 * ditetapkan/diverifikasi" dan harus memicu Data Gap Warning, bukan
 * diganti angka asal.
 *
 * Kolom c dispesifikasi di brief. Kolom e/f masih berderet 4 dan 7 di
 * `jenis_produk_pinjaman` tetapi tabel sandi OJK-nya belum tersedia,
 * jadi pemetaannya sengaja dibiarkan kosong.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Kolom c — Jenis Nasabah
    |--------------------------------------------------------------------------
    | Sesuai brief: 1 = Individu, 2 = Kelompok.
    */
    'jenis_nasabah' => [
        1 => 'Individu',
        2 => 'Kelompok',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolom e — Jenis Penggunaan
    |--------------------------------------------------------------------------
    | Sumber: jenis_produk_pinjaman.jenis. Tabel sandi OJK belum diterima.
    */
    'jenis_penggunaan' => [],

    /*
    |--------------------------------------------------------------------------
    | Kolom f — Sektor Usaha
    |--------------------------------------------------------------------------
    | Sumber: jenis_produk_pinjaman.usaha. Tabel sandi OJK belum diterima.
    */
    'sektor_usaha' => [],

    /*
    |--------------------------------------------------------------------------
    | Kolom g — Periode Pembayaran
    |--------------------------------------------------------------------------
    | Diturunkan dari sistem_angsuran.jenis + sistem_angsuran.sistem
    | (lihat OjkSandi::periodePembayaran()).
    | Brief hanya menyebut kode 1, 2, 3, dan 6; kode 4 (Selapanan) dan
    | 5 (Musiman) memang tidak pernah dihasilkan oleh aturan tersebut.
    */
    'periode_pembayaran' => [
        1 => 'Harian',
        2 => 'Mingguan',
        3 => 'Bulanan',
        6 => 'Tahunan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolom m — Kualitas / Kolektibilitas
    |--------------------------------------------------------------------------
    | Ambang DPD (hari) disepakati memakai tabel kolektibilitas 41/2024
    | yang sudah dipakai view seojk/kolekbilidad_pinjaman_41:
    *
    |   DPD   0..9   Lancar
    |   DPD  10..89  Dalam Perhatian Khusus
    |   DPD  90..119 Kurang Lancar
    |   DPD 120..179 Diragukan
    |   DPD >=180    Macet
    |
    | AMBANG DI ATAS SUDAH DISEPAKATI. Kode sandi yang dicetak di kolom m
    | belum ditetapkan — isi 'kolektibilitas_sandi' setelah tabel sandi
    | OJK tersedia. Selama itu kosong, kolom m menampilkan NAMA kategori,
    | bukan angka.
    */
    'kolektibilitas_sandi' => [],

    'kolektibilitas_ambang' => [
        'nama' => [
            'Lancar',
            'Dalam Perhatian Khusus',
            'Kurang Lancar',
            'Diragukan',
            'Macet',
        ],
        // batas atas (hari) tiap kategori; kategori terakhir = >= nilai ini
        'hari' => [10, 90, 120, 180, PHP_INT_MAX],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolom n — Jenis Agunan / Penjaminan Kredit
    |--------------------------------------------------------------------------
    | Memetakan kode agunan internal aplikasi (jaminan.jenis_jaminan)
    | ke sandi OJK. Belum ada kode OJK yang terverifikasi, jadi semua
    | pemetaan kosong — baris yang butuh kolom n akan masuk Data Gap.
    */
    'agunan' => [
        1 => null, // Surat Tanah / SHM
        2 => null, // BPKB / kendaraan bermotor
        3 => null, // SK Pegawai
        4 => null, // Lain-lain
        5 => null, // Surat Tanah dan Bangunan
    ],

    /*
    |--------------------------------------------------------------------------
    | Format angka & tanggal
    |--------------------------------------------------------------------------
    */
    'format_uang' => '#,##0.00',
    'format_tanggal' => 'Y-m-d',
];
