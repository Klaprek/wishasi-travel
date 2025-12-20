<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\pesanan;
use App\Models\rating;
use Illuminate\Http\Request;

/**
 * Controller pelanggan untuk memberi rating dan ulasan pada pesanan.
 */
class RatingController extends Controller
{
    /**
     * Membuat rating dan ulasan pengguna untuk pesanan.
     *
     * @param Request $request
     * @param pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function menyimpanRating(Request $request, pesanan $pesanan)
    {
        $request->validate([
            'nilai_rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string',
        ]);

        if ($pesanan->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($pesanan->status_pesanan !== 'pesanan_selesai') {
            if (! $request->expectsJson()) {
                return redirect('/pesanan-saya')->with('info', 'Pesanan belum selesai, rating belum bisa diberikan');
            }

            return response()->json([
                'message' => 'Pesanan belum selesai, rating belum bisa diberikan',
            ], 422);
        }

        $existing = rating::where('user_id', auth()->id())
            ->where('pesanan_id', $pesanan->id)
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Rating sudah pernah diberikan',
                    'data' => $existing,
                ], 409);
            }

            return redirect('/pesanan-saya?status=pesanan_selesai')
                ->with('info', 'Rating sudah pernah diberikan untuk pesanan ini');
        }

        $rating = rating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'pesanan_id' => $pesanan->id,
            ],
            [
                'paket_id' => $pesanan->paket_id,
                'nilai_rating' => $request->nilai_rating,
                'ulasan' => $request->ulasan,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Terima kasih atas ulasan Anda!',
                'data' => $rating,
            ]);
        }

        return redirect('/pesanan-saya?status=pesanan_selesai')
            ->with('success', 'Terima kasih atas ulasan Anda!');
    }
}
