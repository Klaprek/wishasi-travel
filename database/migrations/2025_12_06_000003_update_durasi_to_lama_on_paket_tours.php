<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pakettour', function (Blueprint $table) {
            if (Schema::hasColumn('pakettour', 'durasi')) {
                $table->dropColumn('durasi');
            }

            if (! Schema::hasColumn('pakettour', 'lama_hari')) {
                $table->integer('lama_hari')->after('kuota');
            }

            if (! Schema::hasColumn('pakettour', 'lama_malam')) {
                $table->integer('lama_malam')->after('lama_hari');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pakettour', function (Blueprint $table) {
            if (Schema::hasColumn('pakettour', 'lama_hari')) {
                $table->dropColumn('lama_hari');
            }

            if (Schema::hasColumn('pakettour', 'lama_malam')) {
                $table->dropColumn('lama_malam');
            }

            if (! Schema::hasColumn('pakettour', 'durasi')) {
                $table->integer('durasi')->after('kuota');
            }
        });
    }
};
