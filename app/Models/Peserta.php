<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\pesanan;

class peserta extends Model
{
    protected $table = 'peserta';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'pesanan_id',
        'nama_lengkap',
        'alamat',
        'telepon',
        'email',
        'foto_identitas',
        'foto_paspor',
    ];

    public $timestamps = false;

    public const UPDATED_AT = null;

    public function pesanan()
    {
        return $this->belongsTo(pesanan::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $peserta) {
            if (empty($peserta->id)) {
                $peserta->id = self::generateKode($peserta);
            }
        });
    }

    protected static function generateKode(self $peserta): ?string
    {
        $pesanan = $peserta->pesanan ?? ($peserta->pesanan_id ? pesanan::find($peserta->pesanan_id) : null);
        if (!$pesanan) {
            return null;
        }

        $pesananDate = optional($pesanan->created_at)->format('Ymd') ?: now()->format('Ymd');
        $pesananSeq = null;
        if ($pesanan->id && preg_match('/PSN-(\\d{8})-(\\d{3})/', $pesanan->id, $matches)) {
            $pesananDate = $matches[1];
            $pesananSeq = $matches[2];
        } else {
            $pesananSeq = str_pad((string) $pesanan->id, 3, '0', STR_PAD_LEFT);
        }

        $existingCount = self::where('pesanan_id', $pesanan->id)->count();
        $urutanPeserta = str_pad((string) ($existingCount + 1), 2, '0', STR_PAD_LEFT);

        return "PST-{$pesananDate}-{$pesananSeq}-P{$urutanPeserta}";
    }
}
