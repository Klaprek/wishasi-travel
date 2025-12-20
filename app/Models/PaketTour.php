<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class pakettour extends Model
{
    protected $table = 'pakettour';

    protected $fillable = [
        'nama_paket',
        'banner',
        'destinasi',
        'include',
        'harga_per_peserta',
        'jadwal_keberangkatan',
        'kuota',
        'lama_hari',
        'lama_malam',
        'wajib_paspor',
        'wajib_identitas',
        'tampil_di_katalog'
    ];

    protected $casts = [
        'jadwal_keberangkatan' => 'date',
        'wajib_paspor' => 'boolean',
        'wajib_identitas' => 'boolean',
        'tampil_di_katalog' => 'boolean',
        'harga_per_peserta' => 'decimal:2',
    ];

    protected $appends = [
        'banner_url',
        'kuota_terpakai',
        'sisa_kuota',
    ];

    public function pesanan()
    {
        return $this->hasMany(pesanan::class, 'paket_id');
    }

    public function rating()
    {
        return $this->hasMany(rating::class, 'paket_id');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner ? Storage::url($this->banner) : null;
    }

    public function getKuotaTerpakaiAttribute(): int
    {
        if (array_key_exists('kuota_terpakai', $this->attributes)) {
            return (int) $this->attributes['kuota_terpakai'];
        }

        return (int) $this->pesanan()
            ->whereIn('status_pesanan', pesanan::STATUS_KUOTA_TERPAKAI)
            ->sum('jumlah_peserta');
    }

    public function getSisaKuotaAttribute(): int
    {
        $kuota = (int) ($this->kuota ?? 0);
        $terpakai = (int) $this->kuota_terpakai;
        $sisa = $kuota - $terpakai;

        return $sisa > 0 ? $sisa : 0;
    }
}
