<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jenis_simpanan', 'saldo_minimal')) {
            Schema::table('jenis_simpanan', function (Blueprint $table) {
                $table->bigInteger('saldo_minimal')->default(20000)->after('file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jenis_simpanan', 'saldo_minimal')) {
            Schema::table('jenis_simpanan', function (Blueprint $table) {
                $table->dropColumn('saldo_minimal');
            });
        }
    }
};