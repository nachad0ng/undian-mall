<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'nominal_per_poin_snapshot',
        'poin_dari_nominal',
        'poin_bonus_pembayaran',
        'bonus_rule_id_snapshot',
        'bonus_mode_snapshot',
        'bonus_multiplier_snapshot',
        'payment_type_code_snapshot',
        'payment_type_name_snapshot',
        'total_poin_didapat',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
            'nominal_struk' => 'integer',
            'nominal_per_poin_snapshot' => 'integer',
            'poin_dari_nominal' => 'integer',
            'poin_bonus_pembayaran' => 'integer',
            'bonus_rule_id_snapshot' => 'integer',
            'bonus_multiplier_snapshot' => 'decimal:2',
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

    public function raffleTickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
}
