<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\pesanan;
use Illuminate\Http\Request;

/**
 * Controller admin untuk melihat detail dan daftar pesanan.
 */
class KelolaPesananController extends Controller
{
    /**
     * Menampilkan semua pesanan beserta relasi pendukung.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function ambilDataPesanan(Request $request)
    {
        // show all pesanan by group paket
        $pesanan = pesanan::with('paketTour', 'user', 'pesertas')->latest()->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $pesanan]);
        }

        return view('app');
    }

    /**
     * Menampilkan detail satu pesanan.
     *
     * @param Request $request
     * @param pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function ambilDetailPesanan(Request $request, pesanan $pesanan)
    {
        $pesanan->load('pesertas');

        if ($request->expectsJson()) {
            return response()->json(['data' => $pesanan]);
        }

        return view('app');
    }

    /**
     * Mengambil data peserta untuk satu pesanan.
     *
     * @param Request $request
     * @param pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function ambilDataPeserta(Request $request, pesanan $pesanan)
    {
        return $this->ambilDetailPesanan($request, $pesanan);
    }

    /**
     * Menandai pesanan telah diverifikasi oleh admin.
     *
     * @param pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function verifikasiPesanan(pesanan $pesanan)
    {
        $pesanan->update([
            'status_pesanan' => 'menunggu_pembayaran',
            'alasan_penolakan' => null,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Pesanan diverifikasi',
                'pesanan' => $pesanan->fresh('pesertas'),
            ]);
        }

        return back()->with('success', 'Pesanan diverifikasi');
    }

    /**
     * Menolak pesanan dan menyimpan alasan penolakan.
     *
     * @param Request $request
     * @param pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function tolakPesanan(Request $request, pesanan $pesanan)
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string',
        ]);

        $pesanan->update([
            'status_pesanan' => 'pesanan_ditolak',
            'alasan_penolakan' => $validated['alasan_penolakan'],
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Pesanan ditolak',
                'pesanan' => $pesanan->fresh('pesertas'),
            ]);
        }

        return back()->with('error', 'Pesanan ditolak');
    }
}
