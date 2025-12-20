<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    public const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'id_pesanan',
        'channel_pembayaran',
        'status_pembayaran',
        'jumlah_pembayaran',
        'id_transaksi_midtrans',
        'waktu_dibayar',
    ];

    protected $casts = [
        'jumlah_pembayaran' => 'integer',
        'created_at' => 'datetime',
        'waktu_dibayar' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(user::class);
    }

    public function pesanan()
    {
        return $this->belongsTo(pesanan::class, 'id_pesanan');
    }
}
