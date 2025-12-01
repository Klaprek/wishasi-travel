<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PaymentController extends Controller
{
    protected function bootMidtrans(): void
    {
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    public function createSnapToken(Request $request, Pesanan $pesanan)
    {
        $user = $request->user();

        if (! $user || ($user->id !== $pesanan->user_id && !in_array($user->role, ['admin', 'owner']))) {
            abort(403);
        }

        $pesanan->load('paketTour', 'user');

        if (!config('services.midtrans.server_key') || !config('services.midtrans.client_key')) {
            return response()->json([
                'message' => 'Konfigurasi Midtrans belum disetel',
            ], 202);
        }

        $this->bootMidtrans();

        $orderId = 'pesanan-'.$pesanan->id.'-'.time();
        $grossAmount = (int) ($pesanan->paketTour->harga_per_peserta * $pesanan->jumlah_peserta);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $pesanan->user->name ?? 'Customer',
                'email' => $pesanan->user->email ?? null,
                'phone' => $pesanan->user->phone ?? null,
            ],
            'item_details' => [
                [
                    'id' => $pesanan->paketTour->id,
                    'price' => (int) $pesanan->paketTour->harga_per_peserta,
                    'quantity' => $pesanan->jumlah_peserta,
                    'name' => $pesanan->paketTour->nama_paket,
                ],
            ],
            // Redirect setelah pembayaran selesai/pending/error diarahkan kembali ke aplikasi,
            // menghindari default Midtrans (example.com).
            'callbacks' => [
                'finish' => url('/pesanan-saya'),
                'error' => url('/pesanan-saya'),
                'pending' => url('/pesanan-saya'),
            ],
        ];

        $token = Snap::getSnapToken($params);

        return response()->json([
            'token' => $token,
            'order_id' => $orderId,
            'client_key' => config('services.midtrans.client_key'),
        ]);
    }

    public function confirm(Request $request, Pesanan $pesanan)
    {
        $user = $request->user();

        if (! $user || ($user->id !== $pesanan->user_id && !in_array($user->role, ['admin', 'owner']))) {
            abort(403);
        }

        $validated = $request->validate([
            'status_pesanan' => 'nullable|string',
            'transaction_status' => 'nullable|string',
            'payment_type' => 'nullable|string',
        ]);

        $status = $validated['status_pesanan'] ?? 'pembayaran_selesai';

        $pesanan->update([
            'status_pesanan' => $status,
        ]);

        return response()->json([
            'message' => 'Status pembayaran diperbarui',
            'data' => $pesanan->fresh(),
        ]);
    }
}
