<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
        'exchange_start_at',
        'exchange_end_at',
        'status',
        'drawing_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'exchange_start_at' => 'datetime',
            'exchange_end_at' => 'datetime',
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

    public function pointRedemptions(): HasMany
    {
        return $this->hasMany(PointRedemption::class);
    }

    public function bonusRules(): HasMany
    {
        return $this->hasMany(BonusPointRule::class);
    }

    public function canBeActivated(): bool
    {
        return $this->status !== 'closed'
            && $this->drawing_status !== 'completed';
    }

    public function canAcceptTransactions($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : now();

        return $this->status === 'active'
            && $this->drawing_status !== 'completed'
            && $checkDate->between($this->start_at, $this->end_at);
    }

    public function isDrawingCompleted(): bool
    {
        return $this->drawing_status === 'completed';
    }

    public function exchangeOpenNow(): bool
    {
        $now = now();
        $start = $this->exchange_start_at ?? $this->end_at;
        $end = $this->exchange_end_at ?? $this->end_at;

        return $this->status === 'active'
            && $this->drawing_status !== 'completed'
            && $now->between($start, $end);
    }

    public function getActivePrizesForExchange(): Collection
    {
        return $this->prizes()
            ->where('active_for_exchange', true)
            ->where('status', 'active')
            ->get();
    }
}
