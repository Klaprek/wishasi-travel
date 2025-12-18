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
        $hasUserIdIndex = DB::select("SHOW INDEX FROM `ratings` WHERE Key_name = 'ratings_user_id_fk_support_idx'");
        if (empty($hasUserIdIndex)) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->index('user_id', 'ratings_user_id_fk_support_idx');
            });
        }

        $hasPaketIdIndex = DB::select("SHOW INDEX FROM `ratings` WHERE Key_name = 'ratings_paket_id_fk_support_idx'");
        if (empty($hasPaketIdIndex)) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->index('paket_id', 'ratings_paket_id_fk_support_idx');
            });
        }

        if (! Schema::hasColumn('ratings', 'pesanan_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->foreignId('pesanan_id')
                    ->nullable()
                    ->after('paket_id')
                    ->constrained('pesanans')
                    ->onDelete('cascade');
            });
        }

        $hasOldUnique = DB::select("SHOW INDEX FROM `ratings` WHERE Key_name = 'ratings_user_id_paket_id_unique'");
        if (! empty($hasOldUnique)) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'paket_id']);
            });
        }

        DB::table('ratings')
            ->whereNull('pesanan_id')
            ->orderBy('created_at')
            ->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $pesananId = DB::table('pesanans')
                        ->where('user_id', $row->user_id)
                        ->where('paket_id', $row->paket_id)
                        ->orderByDesc('created_at')
                        ->value('id');

                    if (! $pesananId) {
                        continue;
                    }

                    DB::table('ratings')
                        ->where('user_id', $row->user_id)
                        ->where('paket_id', $row->paket_id)
                        ->whereNull('pesanan_id')
                        ->update(['pesanan_id' => $pesananId]);
                }
            });

        $hasNewUnique = DB::select("SHOW INDEX FROM `ratings` WHERE Key_name = 'ratings_user_id_pesanan_id_unique'");
        if (empty($hasNewUnique)) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique(['user_id', 'pesanan_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'pesanan_id']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->unique(['user_id', 'paket_id']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pesanan_id');
        });
    }
};
