<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
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
        return $this->belongsTo(User::class);
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function paketTour()
    {
        return $this->belongsTo(Pakettour::class, 'paket_id');
    }
}
