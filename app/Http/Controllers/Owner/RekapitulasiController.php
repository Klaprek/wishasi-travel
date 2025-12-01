<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class RekapitulasiController extends Controller
{
    public function index(Request $request)
    {
        $rekap = Pesanan::selectRaw('paket_id, COUNT(*) as total')
            ->groupBy('paket_id')
            ->with('paketTour')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $rekap]);
        }

        return view('app');
    }
}
