<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class PesertaController extends Controller
{
    public function create(Pesanan $pesanan)
    {
        return view('customer.peserta.create', compact('pesanan'));
    }

    public function store(Request $request, Pesanan $pesanan)
    {
        foreach ($request->peserta as $data) {
            Peserta::create([
                'pesanan_id' => $pesanan->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'alamat' => $data['alamat'],
                'telepon' => $data['telepon'],
                'email' => $data['email'],
                'foto_identitas' => $data['foto_identitas'] ?? null,
                'paspor' => $data['paspor'] ?? null,
                'status_verifikasi' => 'belum'
            ]);
        }

        $pesanan->update(['status_pesanan' => 'menunggu_verifikasi']);

        return redirect('/pesanan-saya')->with('success', 'Data peserta berhasil disimpan');
    }
}
