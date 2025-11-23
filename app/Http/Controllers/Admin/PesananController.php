<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index()
    {
        // show all pesanan by group paket
        $pesanan = Pesanan::with('paketTour', 'user')->latest()->get();
        return view('admin.pesanan.index', compact('pesanan'));
    }

    public function show(Pesanan $pesanan)
    {
        $pesanan->load('pesertas');
        return view('admin.pesanan.show', compact('pesanan'));
    }
}
