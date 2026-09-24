<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sistem_angsuran', 'jenis')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->enum('jenis', ['bulanan', 'harian', 'bulanan_ditunda'])
                    ->after('deskripsi_sistem');
            });
        }

        if (! Schema::hasColumn('sistem_angsuran', 'interval_hari')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->unsignedSmallInteger('interval_hari')->nullable()
                    ->after('sistem');
            });
        }

        if (! Schema::hasColumn('sistem_angsuran', 'tunda_bulan')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->unsignedTinyInteger('tunda_bulan')->nullable()
                    ->after('interval_hari');
            });
        }

        DB::table('sistem_angsuran')->where('id', 1)->update([
            'jenis' => 'bulanan',
            'tunda_bulan' => null,
        ]);

        DB::table('sistem_angsuran')->whereIn('id', [11])->update([
            'jenis' => 'bulanan_ditunda',
            'tunda_bulan' => 24,
        ]);
        DB::table('sistem_angsuran')->whereIn('id', [14])->update([
            'jenis' => 'bulanan_ditunda',
            'tunda_bulan' => 3,
        ]);
        DB::table('sistem_angsuran')->whereIn('id', [15])->update([
            'jenis' => 'bulanan_ditunda',
            'tunda_bulan' => 2,
        ]);
        DB::table('sistem_angsuran')->whereIn('id', [20])->update([
            'jenis' => 'bulanan_ditunda',
            'tunda_bulan' => 12,
        ]);

        DB::table('sistem_angsuran')->whereIn('id', [12])->update([
            'jenis' => 'harian',
            'interval_hari' => 7,
            'tunda_bulan' => null,
        ]);
        DB::table('sistem_angsuran')->whereIn('id', [25])->update([
            'jenis' => 'harian',
            'interval_hari' => 14,
            'tunda_bulan' => null,
        ]);

        // Insert baris baru: '1 Harian' (tiap hari). Aman dilakukan via insertOrIgnore
        // agar tidak konflik jika migration dijalankan ulang / data sudah ada.
        DB::table('sistem_angsuran')->insertOrIgnore([
            'id' => 26,
            'nama_sistem' => '1 Harian',
            'deskripsi_sistem' => 'tiap hari',
            'sistem' => 1,
            'jenis' => 'harian',
            'interval_hari' => 1,
            'tunda_bulan' => null,
        ]);

        // Catatan historis:
        // id=26 sebelumnya pernah dipakai sebagai bulanan_ditunda dengan tunda=6 bulan
        // (lihat PinjamanKelompokController::generateRA baris lama). Sekarang setelah
        // refactor dan insertOrIgnore, baris id=26 dipakai untuk '1 Harian'. Server
        // yang sebelumnya pernah isi id=26 dengan tunda=6, kolom jenis & tunda_bulan
        // akan di-overwrite oleh blok insertOrIgnore di bawah; namun jika ingin
        // mempertahankan tunda=6 versi lama, JANGAN jalankan blok insertOrIgnore ini.
    }

    public function down(): void
    {
        if (Schema::hasColumn('sistem_angsuran', 'tunda_bulan')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->dropColumn('tunda_bulan');
            });
        }
        if (Schema::hasColumn('sistem_angsuran', 'interval_hari')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->dropColumn('interval_hari');
            });
        }
        if (Schema::hasColumn('sistem_angsuran', 'jenis')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->dropColumn('jenis');
            });
        }
    }
};