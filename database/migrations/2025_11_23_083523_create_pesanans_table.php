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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('paket_id')->constrained('pakettour')->onDelete('cascade');
            $table->integer('jumlah_peserta');
            $table->enum('status_pesanan', [
                'menunggu_verifikasi',
                'menunggu_pembayaran',
                'pembayaran_selesai',
                'pesanan_selesai',
                'pesanan_ditolak'
            ])->default('menunggu_verifikasi');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
