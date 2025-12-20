<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            Schema::table('rating', function (Blueprint $table) {
                $table->unique('pesanan_id');
            });
            return;
        }

        $hasUnique = DB::select("SHOW INDEX FROM `rating` WHERE Key_name = 'rating_pesanan_id_unique'");
        if (empty($hasUnique)) {
            Schema::table('rating', function (Blueprint $table) {
                $table->unique('pesanan_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            Schema::table('rating', function (Blueprint $table) {
                $table->dropUnique(['pesanan_id']);
            });
            return;
        }

        $hasUnique = DB::select("SHOW INDEX FROM `rating` WHERE Key_name = 'rating_pesanan_id_unique'");
        if (! empty($hasUnique)) {
            Schema::table('rating', function (Blueprint $table) {
                $table->dropUnique('rating_pesanan_id_unique');
            });
        }
    }
};
