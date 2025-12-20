<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\CoreApi;

/**
 * Controller untuk menangani alur pembayaran Midtrans.
 */
class PaymentController extends Controller
{
    /**
     * Menyetel konfigurasi Midtrans secara global.
     *
     * @return void
     */
    protected function bootMidtrans(): void
    {
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    /**
     * Normalisasi status transaksi Midtrans ke status pembayaran lokal.
     *
     * @param string|null $transactionStatus
     * @return string
     */
    protected function mapPembayaranStatus(?string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'settlement', 'capture' => 'settlement',
            'expire', 'cancel', 'deny' => 'expire',
            default => 'pending',
        };
    }

    /**
     * Menyimpan atau memperbarui data pembayaran berdasarkan order Midtrans.
     *
     * @param string $midtransOrderId
     * @param array $data
     * @return void
     */
    protected function simpanDataPembayaran(string $midtransOrderId, array $data): void
    {
        Pembayaran::updateOrCreate(
            ['id_transaksi_midtrans' => $midtransOrderId],
            $data
        );
    }

    /**
     * Membuat Snap token atau charge VA langsung untuk pesanan.
     *
     * @param Request $request
     * @param Pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse
     */
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

        $paymentType = $request->input('payment_type');
        $bank = strtolower($request->input('bank', 'bca'));

        $baseParams = [
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
        ];

        // Jika request bank_transfer, langsung charge Core API agar VA number tersedia
        if ($paymentType === 'bank_transfer') {
            $params = $baseParams;

            if ($bank === 'mandiri') {
                // Mandiri VA melalui e-channel
                $params['payment_type'] = 'echannel';
                $params['echannel'] = [
                    'bill_info1' => 'Payment for '.$pesanan->paketTour->nama_paket,
                    'bill_info2' => 'Tour',
                ];
            } else {
                $params['payment_type'] = 'bank_transfer';
                $params['bank_transfer'] = [
                    'bank' => $bank,
                ];
            }

            $charge = CoreApi::charge($params);
            $midtransOrderId = $charge->order_id ?? $orderId;
            $statusPembayaran = $this->mapPembayaranStatus($charge->transaction_status ?? null);

            $this->simpanDataPembayaran($midtransOrderId, [
                'user_id' => $pesanan->user_id,
                'id_pesanan' => $pesanan->id,
                'channel_pembayaran' => $bank,
                'status_pembayaran' => $statusPembayaran,
                'jumlah_pembayaran' => (int) ($charge->gross_amount ?? $grossAmount),
                'token_pembayaran' => null,
                'waktu_dibayar' => $statusPembayaran === 'settlement' ? now() : null,
            ]);

            return response()->json([
                'order_id' => $midtransOrderId,
                'transaction_status' => $charge->transaction_status ?? null,
                'va_numbers' => $charge->va_numbers ?? [],
                'permata_va_number' => $charge->permata_va_number ?? null,
                'bill_key' => $charge->bill_key ?? null,
                'biller_code' => $charge->biller_code ?? null,
                'company_code' => $charge->bill_key ?? $charge->company_code ?? $charge->biller_code ?? null,
                'gross_amount' => $charge->gross_amount ?? $grossAmount,
            ]);
        }

        // default: Snap token
        $params = array_merge($baseParams, [
            'callbacks' => [
                'finish' => url('/pesanan-saya'),
                'error' => url('/pesanan-saya'),
                'pending' => url('/pesanan-saya'),
            ],
        ]);

        $token = Snap::getSnapToken($params);

        $this->simpanDataPembayaran($orderId, [
            'user_id' => $pesanan->user_id,
            'id_pesanan' => $pesanan->id,
            'channel_pembayaran' => $paymentType ?: 'snap',
            'status_pembayaran' => 'pending',
            'jumlah_pembayaran' => $grossAmount,
            'token_pembayaran' => $token,
            'waktu_dibayar' => null,
        ]);

        return response()->json([
            'token' => $token,
            'order_id' => $orderId,
            'client_key' => config('services.midtrans.client_key'),
        ]);
    }

    /**
     * Mengonfirmasi status pembayaran secara manual.
     *
     * @param Request $request
     * @param Pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Mengecek status transaksi ke Midtrans dan memperbarui pesanan.
     *
     * @param Request $request
     * @param Pesanan $pesanan
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request, Pesanan $pesanan)
    {
        $orderId = $request->input('order_id');

        if (! $orderId) {
            return response()->json(['message' => 'order_id wajib diisi'], 422);
        }

        $this->bootMidtrans();

        try {
            $status = Transaction::status($orderId);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil status transaksi'], 500);
        }

        $transactionStatus = $status->transaction_status ?? null;
        $vaNumbers = $status->va_numbers ?? [];
        $companyCode = $status->bill_key ?? $status->company_code ?? $status->biller_code ?? null;
        $amount = $status->gross_amount ?? null;

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $pesanan->update(['status_pesanan' => 'pembayaran_selesai']);
        } elseif ($transactionStatus === 'pending') {
            $pesanan->update(['status_pesanan' => 'menunggu_pembayaran']);
        }

        $statusPembayaran = $this->mapPembayaranStatus($transactionStatus);
        $existingPembayaran = Pembayaran::where('id_transaksi_midtrans', $orderId)->first();
        $amountValue = $amount;
        if ($amountValue === null) {
            $pesanan->loadMissing('paketTour');
            $amountValue = (int) (($pesanan->paketTour->harga_per_peserta ?? 0) * ($pesanan->jumlah_peserta ?? 1));
        }
        $waktuDibayar = $statusPembayaran === 'settlement'
            ? ($existingPembayaran?->waktu_dibayar ?? now())
            : null;

        $this->simpanDataPembayaran($orderId, [
            'user_id' => $pesanan->user_id,
            'id_pesanan' => $pesanan->id,
            'channel_pembayaran' => $existingPembayaran?->channel_pembayaran,
            'status_pembayaran' => $statusPembayaran,
            'jumlah_pembayaran' => (int) ($amountValue ?? 0),
            'token_pembayaran' => $existingPembayaran?->token_pembayaran,
            'waktu_dibayar' => $waktuDibayar,
        ]);

        return response()->json([
            'transaction_status' => $transactionStatus,
            'va_numbers' => $vaNumbers,
            'company_code' => $companyCode,
            'bill_key' => $status->bill_key ?? null,
            'biller_code' => $status->biller_code ?? null,
            'gross_amount' => $amount,
            'order_id' => $orderId,
            'pesanan' => $pesanan->fresh(),
        ]);
    }
}
