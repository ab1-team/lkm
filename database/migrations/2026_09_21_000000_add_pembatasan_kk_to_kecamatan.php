<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kecamatan', 'pembatasan_kk')) {
            Schema::table('kecamatan', function (Blueprint $table) {
                $table->tinyInteger('pembatasan_kk')->nullable()->after('hak_kredit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kecamatan', 'pembatasan_kk')) {
            Schema::table('kecamatan', function (Blueprint $table) {
                $table->dropColumn('pembatasan_kk');
            });
        }
    }
};