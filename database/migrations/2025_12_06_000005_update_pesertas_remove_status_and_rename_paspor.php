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
        Schema::table('peserta', function (Blueprint $table) {
            if (Schema::hasColumn('peserta', 'status_verifikasi')) {
                $table->dropColumn('status_verifikasi');
            }

            if (Schema::hasColumn('peserta', 'paspor')) {
                DB::statement("ALTER TABLE peserta CHANGE paspor foto_paspor VARCHAR(255) NULL");
            }

            if (Schema::hasColumn('peserta', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            if (! Schema::hasColumn('peserta', 'status_verifikasi')) {
                $table->enum('status_verifikasi', ['belum', 'diverifikasi', 'ditolak'])->default('belum')->after('foto_paspor');
            }

            if (Schema::hasColumn('peserta', 'foto_paspor')) {
                DB::statement("ALTER TABLE peserta CHANGE foto_paspor paspor VARCHAR(255) NULL");
            }

            if (! Schema::hasColumn('peserta', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }
};
