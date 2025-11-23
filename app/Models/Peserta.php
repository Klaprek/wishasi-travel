<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    protected $fillable = [
        'pesanan_id',
        'nama_lengkap',
        'alamat',
        'telepon',
        'email',
        'foto_identitas',
        'paspor',
        'status_verifikasi',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }
}