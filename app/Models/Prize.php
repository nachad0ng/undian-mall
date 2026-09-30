<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_period_id',
        'name',
        'description',
        'nominal_per_poin',
        'sequence',
        'quantity',
        'status',
        'active_for_exchange',
        'ticket_digits',
        'ticket_counter',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'sequence' => 'integer',
            'nominal_per_poin' => 'integer',
            'active_for_exchange' => 'boolean',
            'ticket_digits' => 'integer',
            'ticket_counter' => 'integer',
        ];
    }

    public function rafflePeriod(): BelongsTo
    {
        return $this->belongsTo(RafflePeriod::class);
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

    public function raffleTickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }

    public function customerBalances(): HasMany
    {
        return $this->hasMany(CustomerPointBalance::class, 'prize_id');
    }

    public function isUsedInDrawing(): bool
    {
        return $this->drawings()->exists() || $this->winners()->exists();
    }

    public function canBeExchanged(): bool
    {
        return $this->active_for_exchange
            && $this->status === 'active'
            && $this->nominal_per_poin !== null
            && $this->nominal_per_poin > 0;
    }
}
