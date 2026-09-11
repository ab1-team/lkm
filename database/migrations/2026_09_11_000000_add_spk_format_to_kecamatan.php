<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kecamatan', 'spk_format')) {
            Schema::table('kecamatan', function (Blueprint $table) {
                $table->text('spk_format')->nullable()->after('redaksi_spk');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kecamatan', 'spk_format')) {
            Schema::table('kecamatan', function (Blueprint $table) {
                $table->dropColumn('spk_format');
            });
        }
    }
};
