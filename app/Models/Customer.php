<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'identity_number',
        'email',
        'address',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function pointRedemptions(): HasMany
    {
        return $this->hasMany(PointRedemption::class);
    }

    public function pointBalances(): HasMany
    {
        return $this->hasMany(CustomerPointBalance::class);
    }

    public function getPointBalanceForPeriodAndPrize(int $periodId, int $prizeId): ?CustomerPointBalance
    {
        return $this->pointBalances()
            ->where('raffle_period_id', $periodId)
            ->where('prize_id', $prizeId)
            ->first();
    }
}
