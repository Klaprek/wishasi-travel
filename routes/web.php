<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public controllers
use App\Http\Controllers\PaketTourController;

// Customer controllers
use App\Http\Controllers\Customer\PesananController;
use App\Http\Controllers\Customer\PesertaController;
use App\Http\Controllers\Customer\RatingController;

// Admin controllers
use App\Http\Controllers\Admin\KelolaPaketController;
use App\Http\Controllers\Admin\KelolaPesananController;

// Owner controllers
use App\Http\Controllers\Owner\RekapitulasiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PaymentController;

// -----------------------------------------------------
// LANDING PAGE & KATALOG
// -----------------------------------------------------

Route::get('/', [PaketTourController::class, 'ambilDataPaket']);
Route::get('/paket/{paketTour}', [PaketTourController::class, 'ambilDataDetail']);
Route::get('/api/paket', [PaketTourController::class, 'ambilDataPaket']);
Route::get('/api/paket/{paketTour}', [PaketTourController::class, 'ambilDataDetail']);
Route::get('/api/ratings', [PaketTourController::class, 'ambilDataRating']);

// -----------------------------------------------------
// SOCIAL AUTH (GOOGLE)
// -----------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [AuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');
});


// -----------------------------------------------------
// DASHBOARD BREEZE
// -----------------------------------------------------

Route::get('/dashboard', function () {
    return view('app');
})->middleware(['auth', 'verified'])->name('dashboard');


// -----------------------------------------------------
// PROFILE
// -----------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/api/me', [AuthController::class, 'ambilDataUser']);
});


// -----------------------------------------------------
// CUSTOMER
// -----------------------------------------------------

Route::middleware(['auth', 'role:customer'])->group(function () {

    Route::get('/pesanan-saya', [PesananController::class, 'index']);
    Route::post('/pesan/{paketTour}', [PesananController::class, 'simpanDataPesanan']);

    Route::get('/pesanan/{pesanan}/peserta', function () {
        return view('app');
    });
    Route::post('/pesanan/{pesanan}/peserta', [PesertaController::class, 'simpanDataPeserta']);
    Route::post('/pesanan/{pesanan}/selesai', [PesananController::class, 'markSelesai']);

    Route::post('/pesanan/{pesanan}/rating', [RatingController::class, 'menyimpanRating'])
        ->name('rating.store');

    Route::post('/payments/{pesanan}/snap-token', [PaymentController::class, 'createSnapToken']);
    Route::post('/payments/{pesanan}/confirm', [PaymentController::class, 'confirm']);
    Route::get('/payments/{pesanan}/status', [PaymentController::class, 'status']);
});


// -----------------------------------------------------
// ADMIN
// -----------------------------------------------------

Route::middleware(['auth','role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // resource route untuk kelola paket
        Route::get('/paket', [KelolaPaketController::class, 'ambilDataPaket'])
            ->name('paket.index');
        Route::post('/paket', [KelolaPaketController::class, 'simpanDataPaket'])
            ->name('paket.store');
        Route::put('/paket/{paketTour}', [KelolaPaketController::class, 'editDataPaket'])
            ->name('paket.update');
        Route::get('/paket/{paketTour}/edit', [KelolaPaketController::class, 'ambilDataDetail'])
            ->name('paket.edit');
        Route::delete('/paket/{paketTour}', [KelolaPaketController::class, 'hapusDataPaket'])
            ->name('paket.destroy');
        Route::resource('paket', KelolaPaketController::class)
            ->except(['index', 'store', 'edit', 'create', 'update', 'destroy'])
            ->parameters(['paket' => 'paketTour']);

        // hide & show paket
        Route::put('/paket/{paketTour}/hide', [KelolaPaketController::class, 'menyembunyikanDataPaket'])
            ->name('paket.hide');
        Route::put('/paket/{paketTour}/show', [KelolaPaketController::class, 'tampilkanDataPaket'])
            ->name('paket.visible');

        // kelola pesanan
        Route::get('/pesanan', [KelolaPesananController::class, 'ambilDataPesanan'])->name('pesanan.index');
        Route::get('/pesanan/{pesanan}', [KelolaPesananController::class, 'ambilDetailPesanan'])->name('pesanan.show');
        Route::get('/pesanan/{pesanan}/peserta', [KelolaPesananController::class, 'ambilDataPeserta'])
            ->name('pesanan.peserta');

        // verifikasi peserta
        Route::put('/pesanan/{pesanan}/verify', [KelolaPesananController::class, 'verifikasiPesanan'])
            ->name('pesanan.verify');
        Route::put('/pesanan/{pesanan}/reject', [KelolaPesananController::class, 'tolakPesanan'])
            ->name('pesanan.reject');
    });


// -----------------------------------------------------
// OWNER
// -----------------------------------------------------

Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/owner/rekapitulasi', [RekapitulasiController::class, 'ambilDataRekap'])
        ->name('owner.rekapitulasi');
});

require __DIR__.'/auth.php';

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
