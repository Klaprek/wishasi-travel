<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rating extends Model
{
    protected $table = 'rating';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'pesanan_id',
        'paket_id',
        'nilai_rating',
        'ulasan',
    ];

    public function user()
    {
        return $this->belongsTo(user::class);
    }

    public function pesanan()
    {
        return $this->belongsTo(pesanan::class);
    }

    public function paketTour()
    {
        return $this->belongsTo(pakettour::class, 'paket_id');
    }
}
