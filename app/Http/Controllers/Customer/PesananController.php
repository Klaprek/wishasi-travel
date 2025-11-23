<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\PaketTour;

class PesananController extends Controller
{
    public function index()
    {
        $pesanan = Pesanan::where('user_id', auth()->id())->latest()->get();
        return view('customer.pesanan.index', compact('pesanan'));
    }

    public function store(Request $request, PaketTour $paketTour)
    {
        $request->validate([
            'jumlah_peserta' => 'required|integer|min:1'
        ]);

        $pesanan = Pesanan::create([
            'user_id' => auth()->id(),
            'paket_id' => $paketTour->id,
            'jumlah_peserta' => $request->jumlah_peserta,
            'status_pesanan' => 'menunggu_verifikasi'
        ]);

        return redirect('/pesanan-saya');
    }
}