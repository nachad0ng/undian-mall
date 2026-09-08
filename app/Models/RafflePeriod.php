<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RafflePeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'start_at',
        'end_at',
        'purchase_threshold',
        'coupon_unit',
        'max_coupon_per_transaction',
        'status',
        'drawing_status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'purchase_threshold' => 'integer',
            'coupon_unit' => 'integer',
            'max_coupon_per_transaction' => 'integer',
        ];
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function drawings(): HasMany
    {
        return $this->hasMany(Drawing::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function hasCouponRule(): bool
    {
        return (int) $this->purchase_threshold >= 1 && (int) $this->coupon_unit >= 1;
    }

    public function canBeActivated(): bool
    {
        return $this->status !== 'closed'
            && $this->drawing_status !== 'completed'
            && $this->hasCouponRule();
    }

    /**
     * Memeriksa apakah periode aktif dan tanggal transaksi berada dalam rentang periode.
     */
    public function canAcceptTransactions($date = null): bool
    {
        $checkDate = $date ? \Carbon\Carbon::parse($date) : now();

        return $this->status === 'active'
            && $this->drawing_status !== 'completed'
            && $this->hasCouponRule()
            && $checkDate->between($this->start_at, $this->end_at);
    }

    /**
     * Memeriksa apakah pengundian pada periode ini telah selesai.
     */
    public function isDrawingCompleted(): bool
    {
        return $this->drawing_status === 'completed';
    }

    /**
     * Menghitung kupon secara deterministik berdasarkan konfigurasi periode.
     */
    public function calculateCoupons(int $amount): int
    {
        if ($amount < $this->purchase_threshold || $this->coupon_unit <= 0) {
            return 0;
        }

        $coupons = intdiv($amount, $this->coupon_unit);

        if ($this->max_coupon_per_transaction && $coupons > $this->max_coupon_per_transaction) {
            return $this->max_coupon_per_transaction;
        }

        return $coupons;
    }
}
