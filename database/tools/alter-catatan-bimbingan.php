<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Hanya bisa dijalankan via CLI.');
}

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$database = DB::connection()->getDatabaseName();

// Nama kolom diambil dari controller (sumber kebenaran), bukan diketik manual,
// supaya tidak pernah salah eja lagi.
$ctrl = file_get_contents(__DIR__.'/../../app/Http/Controllers/PinjamanKelompokController.php');
preg_match("/'catatan_b[a-z_]*'\s*=>\s*json_encode/", $ctrl, $m);
preg_match('/catatan_b[a-z_]*/', $m[0], $n);
$kolom = $n[0];

echo 'Database : '.$database."\n";
echo 'Kolom   : '.$kolom.' (len '.strlen($kolom).")\n\n";

// Dua pola tabel: pinjaman kelompok dan pinjaman anggota (individu).
$pola = ['pinjaman_kelompok_[0-9]+', 'pinjaman_anggota_[0-9]+'];

$tables = DB::select(
    "SELECT TABLE_NAME FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = ? AND TABLE_NAME REGEXP ?",
    [$database, '^('.implode('|', $pola).')$']
);

$total = 0;
$skip = 0;
$gagal = 0;

foreach ($tables as $table) {
    $nama = $table->TABLE_NAME;

    $cek = DB::select(
        'SELECT COUNT(*) AS jml FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
        [$database, $nama, $kolom]
    );

    if ($cek[0]->jml > 0) {
        echo "SKIP  $nama (kolom sudah ada)\n";
        $skip++;

        continue;
    }

    try {
        DB::statement("ALTER TABLE `$database`.`$nama` ADD `$kolom` TEXT NULL AFTER `catatan_verifikasi`");
        echo "OK    $nama\n";
        $total++;
    } catch (\Throwable $e) {
        // Tabel dengan data legacy '0000-00-00' ditolak MySQL saat ALTER
        // di sql_mode ketat. Longgarkan hanya untuk sesi ini, lalu pulihkan.
        $sqlModeAsli = DB::selectOne('SELECT @@SESSION.sql_mode m')->m;
        $longgar = str_replace(
            ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'STRICT_TRANS_TABLES'],
            '',
            $sqlModeAsli
        );

        try {
            DB::statement('SET SESSION sql_mode = ?', [$longgar]);
            DB::statement("ALTER TABLE `$database`.`$nama` ADD `$kolom` TEXT NULL AFTER `catatan_verifikasi`");
            echo "OK    $nama (sql_mode dilonggarkan)\n";
            $total++;
        } catch (\Throwable $e2) {
            echo 'GAGAL '.$nama.' - '.$e2->getMessage()."\n";
            $gagal++;
        } finally {
            DB::statement('SET SESSION sql_mode = ?', [$sqlModeAsli]);
        }
    }
}

echo "------------------------\n";
echo 'Total ditambah: '.$total.' | Sudah ada: '.$skip.' | Gagal: '.$gagal."\n";