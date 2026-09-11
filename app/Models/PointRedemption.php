<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'raffle_period_id',
        'prize_id',
        'purchase_id',
        'cs_id',
        'redeemed_at',
        'nominal_struk',
        'total_poin_didapat',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
            'nominal_struk' => 'integer',
            'total_poin_didapat' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rafflePeriod(): BelongsTo
    {
        return $this->belongsTo(RafflePeriod::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function cs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cs_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
}
