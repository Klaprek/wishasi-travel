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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['customer', 'admin', 'owner'])->default('customer');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('pakettour', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket');
            $table->string('banner');
            $table->text('destinasi');
            $table->text('include')->nullable();
            $table->decimal('harga_per_peserta', 12, 2);
            $table->date('jadwal_keberangkatan');
            $table->integer('kuota');
            $table->integer('lama_hari');
            $table->integer('lama_malam');
            $table->boolean('wajib_paspor')->default(false);
            $table->boolean('wajib_identitas')->default(true);
            $table->boolean('tampil_di_katalog')->default(true);
            $table->timestamps();
        });

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

        Schema::create('rating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('paket_id')->constrained('pakettour')->onDelete('cascade');
            $table->string('pesanan_id', 24)->nullable();
            $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
            $table->tinyInteger('nilai_rating');
            $table->text('ulasan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'pesanan_id']);
            $table->unique('pesanan_id');
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->increments('id_pembayaran');
            $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
            $table->string('id_pesanan', 24);
            $table->foreign('id_pesanan')->references('id')->on('pesanan')->cascadeOnDelete();
            $table->string('channel_pembayaran', 50)->nullable();
            $table->enum('status_pembayaran', ['pending', 'settlement', 'expire']);
            $table->integer('jumlah_pembayaran');
            $table->string('id_transaksi_midtrans', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('waktu_dibayar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('rating');
        Schema::dropIfExists('peserta');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('pakettour');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user');
    }
};
