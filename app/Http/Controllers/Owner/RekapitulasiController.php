<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class RekapitulasiController extends Controller
{
    public function index()
    {
        $rekap = Pesanan::selectRaw('paket_id, COUNT(*) as total')
            ->groupBy('paket_id')
            ->with('paketTour')
            ->get();

        return view('owner.rekapitulasi.index', compact('rekap'));
    }
}