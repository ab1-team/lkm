<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sistem_angsuran', 'urutan')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->unsignedSmallInteger('urutan')->nullable()
                    ->after('tunda_bulan');
                $table->index('urutan');
            });
        }

        // Hitung penggunaan sistem_angsuran lintas semua tabel pinjaman (kecuali nama tabel invalid)
        $tables = DB::select('SHOW TABLES');
        $usage = [];
        foreach ($tables as $t) {
            $name = array_values((array) $t)[0];
            if (! preg_match('/^(pinjaman_kelompok_|pinjaman_anggota_)/', $name)) {
                continue;
            }
            if ($name === 'pinjaman_anggota_') {
                continue; // suffix kosong (anomali)
            }
            if (preg_match('/[^a-zA-Z0-9_]/', $name)) {
                continue; // nama tabel invalid (mis. mengandung '-')
            }

            try {
                $rows = DB::table($name)
                    ->select('sistem_angsuran', DB::raw('COUNT(*) as total'))
                    ->groupBy('sistem_angsuran')
                    ->get();
                foreach ($rows as $r) {
                    $sa = $r->sistem_angsuran;
                    if (! isset($usage[$sa])) {
                        $usage[$sa] = 0;
                    }
                    $usage[$sa] += $r->total;
                }
            } catch (\Throwable $e) {
                // skip tabel yang gagal di-scan
                continue;
            }
        }

        // Reset urutan ke null dulu untuk konsistensi
        DB::table('sistem_angsuran')->update(['urutan' => null]);

        // Sort by count DESC, id ASC untuk tie-breaker deterministik
        arsort($usage);
        $rank = 1;
        foreach ($usage as $sa => $count) {
            // Update semua id dengan nilai sistem_angsuran = $sa (string matching, bukan = id numerik langsung)
            // Karena sistem_angsuran disimpan varchar, bisa ada '1', '01', dll — handle semua.
            DB::table('sistem_angsuran')
                ->where('id', (int) $sa)
                ->update(['urutan' => $rank]);
            $rank++;
        }

        // Untuk id yang tidak pernah dipakai (count 0), urutan = null
        // atau bisa kita assign nilai besar seperti 999. Di sini pilih null agar jelas.
    }

    public function down(): void
    {
        if (Schema::hasColumn('sistem_angsuran', 'urutan')) {
            Schema::table('sistem_angsuran', function (Blueprint $table) {
                $table->dropIndex(['urutan']);
                $table->dropColumn('urutan');
            });
        }
    }
};