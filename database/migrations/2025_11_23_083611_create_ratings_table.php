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
        Schema::create('ratings', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('paket_id')->constrained('paket_tours')->onDelete('cascade');
            $table->tinyInteger('nilai_rating'); // 1 - 5
            $table->text('ulasan')->nullable();
            $table->timestamp('tanggal_rating')->useCurrent();
            $table->timestamps();

            // rule: satu rating per user per paket
            $table->unique(['user_id', 'paket_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
