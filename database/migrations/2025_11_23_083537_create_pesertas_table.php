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
        Schema::create('peserta', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('pesanan_id', 24);
            $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->text('alamat');
            $table->string('telepon');
            $table->string('email');
            $table->string('foto_identitas')->nullable();
            $table->string('foto_paspor')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
