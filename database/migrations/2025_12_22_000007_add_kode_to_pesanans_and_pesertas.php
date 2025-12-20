<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('peserta', 'kode')) {
            Schema::table('peserta', function (Blueprint $table) {
                $table->dropUnique(['kode']);
                $table->dropColumn('kode');
            });
        }

        if (Schema::hasColumn('pesanan', 'kode')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropUnique(['kode']);
                $table->dropColumn('kode');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pesanan', 'kode')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->string('kode', 24)->nullable()->unique()->after('id');
            });
        }

        if (! Schema::hasColumn('peserta', 'kode')) {
            Schema::table('peserta', function (Blueprint $table) {
                $table->string('kode', 24)->nullable()->unique()->after('id');
            });
        }
    }
};
