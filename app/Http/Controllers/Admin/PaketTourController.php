<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaketTour;
use Illuminate\Http\Request;

class PaketTourController extends Controller
{
    public function index()
    {
        $paket = PaketTour::all();
        return view('admin.paket.index', compact('paket'));
    }

    public function create()
    {
        return view('admin.paket.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required',
            'harga_per_peserta' => 'required|numeric'
        ]);

        PaketTour::create($request->all());

        return redirect()->route('admin.paket.index');
    }

    public function edit(PaketTour $paketTour)
    {
        return view('admin.paket.edit', compact('paketTour'));
    }

    public function update(Request $request, PaketTour $paketTour)
    {
        $paketTour->update($request->all());
        return redirect()->route('admin.paket.index');
    }

    public function destroy(PaketTour $paketTour)
    {
        $paketTour->delete();
        return redirect()->route('admin.paket.index');
    }

    public function hide(PaketTour $paketTour)
    {
        $paketTour->update(['tampil_di_katalog' => false]);
        return back();
    }

    public function showPaket(PaketTour $paketTour)
    {
        $paketTour->update(['tampil_di_katalog' => true]);
        return back();
    }
}