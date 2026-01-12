<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Rating;
use Illuminate\Http\Request;

/**
 * Controller pemilik untuk melihat rekapitulasi pesanan selesai.
 */
class RekapitulasiController extends Controller
{
    /**
     * Menampilkan rekap pesanan selesai berdasarkan filter bulan/tahun.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function ambilDataRekap(Request $request)
    {
        $query = Pesanan::with(['paketTour', 'user', 'pembayarans'])
            ->whereIn('status_pesanan', ['pembayaran_selesai', 'pesanan_selesai']);

        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->integer('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->integer('tahun'));
        }

        $pesanan = $query->get();
        $pesananIds = $pesanan->pluck('id');
        $ratingRata = $pesananIds->isNotEmpty()
            ? (float) Rating::whereIn('pesanan_id', $pesananIds)->avg('nilai_rating')
            : null;

        $rekap = $pesanan->groupBy('paket_id')->map(function ($items) {
            $paket = $items->first()->paketTour;
            $detail = $items->map(function ($pesanan) {
                $pembayaran = $pesanan->pembayarans
                    ->sortByDesc(function ($item) {
                        return $item->waktu_dibayar ?? $item->created_at ?? $item->id_pembayaran;
                    })
                    ->first();

                return [
                    'customer' => $pesanan->user?->name,
                    'jumlah_peserta' => $pesanan->jumlah_peserta,
                    'harga' => $pesanan->paketTour?->harga_per_peserta,
                    'pembayaran' => $pembayaran?->channel_pembayaran,
                    'jumlah_pembayaran' => $pembayaran?->jumlah_pembayaran,
                    'tanggal_pesanan' => $pesanan->created_at?->toDateString(),
                ];
            })->values();

            return [
                'paket_id' => $paket?->id,
                'paket' => $paket,
                'pesanan' => $detail,
            ];
        })->values();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'rekap' => $rekap,
                    'rating_rata' => $ratingRata,
                ],
            ]);
        }

        return view('app');
    }
}
