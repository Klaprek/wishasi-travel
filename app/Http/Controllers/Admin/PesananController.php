<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        // show all pesanan by group paket
        $pesanan = Pesanan::with('paketTour', 'user', 'pesertas')->latest()->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $pesanan]);
        }

        return view('app');
    }

    public function show(Request $request, Pesanan $pesanan)
    {
        $pesanan->load('pesertas');

        if ($request->expectsJson()) {
            return response()->json(['data' => $pesanan]);
        }

        return view('app');
    }
}
