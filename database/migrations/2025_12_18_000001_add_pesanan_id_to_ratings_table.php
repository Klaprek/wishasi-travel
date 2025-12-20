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
        // Pastikan ada index untuk kolom FK yang sudah ada, agar unique lama bisa di-drop.
        // (Pada beberapa versi MySQL, FK user_id memakai index unique gabungan user_id+paket_id)
        $hasUserIdIndex = DB::select("SHOW INDEX FROM `rating` WHERE Key_name = 'rating_user_id_fk_support_idx'");
        if (empty($hasUserIdIndex)) {
            Schema::table('rating', function (Blueprint $table) {
                $table->index('user_id', 'rating_user_id_fk_support_idx');
            });
        }

        $hasPaketIdIndex = DB::select("SHOW INDEX FROM `rating` WHERE Key_name = 'rating_paket_id_fk_support_idx'");
        if (empty($hasPaketIdIndex)) {
            Schema::table('rating', function (Blueprint $table) {
                $table->index('paket_id', 'rating_paket_id_fk_support_idx');
            });
        }

        if (! Schema::hasColumn('rating', 'pesanan_id')) {
            Schema::table('rating', function (Blueprint $table) {
                $table->string('pesanan_id', 24)->nullable()->after('paket_id');
                $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
            });
        }

        $hasOldUnique = DB::select("SHOW INDEX FROM `rating` WHERE Key_name = 'rating_user_id_paket_id_unique'");
        if (! empty($hasOldUnique)) {
            Schema::table('rating', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'paket_id']);
            });
        }

        DB::table('rating')
            ->whereNull('pesanan_id')
            ->orderBy('created_at')
            ->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $pesananId = DB::table('pesanan')
                        ->where('user_id', $row->user_id)
                        ->where('paket_id', $row->paket_id)
                        ->orderByDesc('created_at')
                        ->value('id');

                    if (! $pesananId) {
                        continue;
                    }

                    DB::table('rating')
                        ->where('user_id', $row->user_id)
                        ->where('paket_id', $row->paket_id)
                        ->whereNull('pesanan_id')
                        ->update(['pesanan_id' => $pesananId]);
                }
            });

        $hasNewUnique = DB::select("SHOW INDEX FROM `rating` WHERE Key_name = 'rating_user_id_pesanan_id_unique'");
        if (empty($hasNewUnique)) {
            Schema::table('rating', function (Blueprint $table) {
                $table->unique(['user_id', 'pesanan_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rating', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'pesanan_id']);
        });

        Schema::table('rating', function (Blueprint $table) {
            $table->unique(['user_id', 'paket_id']);
        });

        Schema::table('rating', function (Blueprint $table) {
            $table->dropForeign(['pesanan_id']);
            $table->dropColumn('pesanan_id');
        });
    }
};
