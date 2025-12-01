<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class PesertaController extends Controller
{
    public function verify(Peserta $peserta)
    {
        $peserta->update([
            'status_verifikasi' => 'diverifikasi'
        ]);

        // jika semua peserta diverifikasi → ubah status pesanan
        $pesanan = $peserta->pesanan;
        $total = $pesanan->pesertas()->count();
        $verified = $pesanan->pesertas()->where('status_verifikasi', 'diverifikasi')->count();

        if ($total == $verified) {
            $pesanan->update([
                'status_pesanan' => 'menunggu_pembayaran'
            ]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Peserta berhasil diverifikasi',
                'data' => $peserta->fresh(),
                'pesanan' => $pesanan->fresh('pesertas'),
            ]);
        }

        return back()->with('success', 'Peserta berhasil diverifikasi');
    }

    public function reject(Peserta $peserta)
    {
        $peserta->update([
            'status_verifikasi' => 'ditolak'
        ]);

        // status pesanan tetap menunggu verifikasi
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Peserta ditolak. Customer diminta upload ulang.',
                'data' => $peserta->fresh(),
            ], 422);
        }

        return back()->with('error', 'Peserta ditolak. Customer diminta upload ulang.');
    }
}
