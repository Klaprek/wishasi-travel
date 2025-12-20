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
        Schema::create('rating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('paket_id')->constrained('pakettour')->onDelete('cascade');
            $table->string('pesanan_id', 24)->nullable();
            $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
            $table->tinyInteger('nilai_rating'); // 1 - 5
            $table->text('ulasan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // rule: satu rating per user per pesanan
            $table->unique(['user_id', 'pesanan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating');
    }
};
