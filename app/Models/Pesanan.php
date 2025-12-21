<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Pesanan extends Model
{
    public const STATUS_KUOTA_TERPAKAI = ['pembayaran_selesai', 'pesanan_selesai'];

    protected $table = 'pesanan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'paket_id',
        'jumlah_peserta',
        'status_pesanan',
        'alasan_penolakan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paketTour()
    {
        return $this->belongsTo(Pakettour::class, 'paket_id');
    }

    public function pesertas()
    {
        return $this->hasMany(Peserta::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'id_pesanan');
    }

    /**
     * Rating yang diberikan pengguna pada pesanan ini (jika ada).
     */
    public function rating()
    {
        return $this->hasOne(Rating::class, 'pesanan_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $pesanan) {
            if (empty($pesanan->id)) {
                $pesanan->id = self::generateKode();
            }
        });

        static::created(function (self $pesanan) {
            if (! $pesanan->paket_id) {
                return;
            }

            self::tolakPesananJikaKuotaPenuh($pesanan->paket_id);
        });

        static::updated(function (self $pesanan) {
            if (! $pesanan->wasChanged('status_pesanan')) {
                return;
            }

            if (! in_array($pesanan->status_pesanan, self::STATUS_KUOTA_TERPAKAI, true)) {
                return;
            }

            if (! $pesanan->paket_id) {
                return;
            }

            self::tolakPesananJikaKuotaPenuh($pesanan->paket_id);
        });
    }

    protected static function tolakPesananJikaKuotaPenuh(int $paketId): void
    {
        $kuota = Pakettour::whereKey($paketId)->value('kuota');
        if ($kuota === null) {
            return;
        }

        $terpakai = self::where('paket_id', $paketId)
            ->whereIn('status_pesanan', self::STATUS_KUOTA_TERPAKAI)
            ->sum('jumlah_peserta');

        if ($terpakai < (int) $kuota) {
            return;
        }

        self::where('paket_id', $paketId)
            ->whereNotIn('status_pesanan', array_merge(self::STATUS_KUOTA_TERPAKAI, ['pesanan_ditolak']))
            ->update([
                'status_pesanan' => 'pesanan_ditolak',
                'alasan_penolakan' => 'Kuota sudah penuh',
            ]);
    }

    protected static function generateKode(): string
    {
        $today = Carbon::now()->format('Ymd');

        $lastKode = self::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('id');

        $lastNumber = 0;
        if ($lastKode && preg_match('/PSN-\\d{8}-(\\d{3})/', $lastKode, $matches)) {
            $lastNumber = (int) $matches[1];
        } else {
            $lastNumber = self::count();
        }

        $next = str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
        return "PSN-{$today}-{$next}";
    }
}
