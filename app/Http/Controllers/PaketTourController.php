<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketTour;

class PaketTourController extends Controller
{
    public function index()
    {
        // hanya tampilkan paket aktif
        $paket = PaketTour::where('tampil_di_katalog', true)->get();
        return view('katalog.index', compact('paket'));
    }

    public function show(PaketTour $paketTour)
    {
        return view('katalog.show', compact('paketTour'));
    }
}
