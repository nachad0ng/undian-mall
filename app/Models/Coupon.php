<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_period_id',
        'purchase_id',
        'customer_id',
        'coupon_number',
        'status',
    ];

    public function rafflePeriod(): BelongsTo
    {
        return $this->belongsTo(RafflePeriod::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function winner(): HasOne
    {
        return $this->hasOne(Winner::class);
    }
}
