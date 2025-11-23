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
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans')->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->text('alamat');
            $table->string('telepon');
            $table->string('email');
            $table->string('foto_identitas')->nullable();
            $table->string('paspor')->nullable();
            $table->enum('status_verifikasi', ['belum', 'diverifikasi', 'ditolak'])->default('belum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesertas');
    }
};
