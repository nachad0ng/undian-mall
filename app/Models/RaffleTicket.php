<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaffleTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_period_id',
        'prize_id',
        'customer_id',
        'point_redemption_id',
        'purchase_id',
        'sequence_number',
        'ticket_number',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
        ];
    }

    public function rafflePeriod(): BelongsTo
    {
        return $this->belongsTo(RafflePeriod::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function redemption(): BelongsTo
    {
        return $this->belongsTo(PointRedemption::class, 'point_redemption_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
