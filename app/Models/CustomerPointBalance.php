<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPointBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'raffle_period_id',
        'prize_id',
        'total_poin',
    ];

    protected function casts(): array
    {
        return [
            'total_poin' => 'integer',
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

    public static function findOrCreateForCustomerPeriodPrize(
        int $customerId,
        int $periodId,
        int $prizeId
    ): self {
        return static::firstOrCreate(
            [
                'customer_id' => $customerId,
                'raffle_period_id' => $periodId,
                'prize_id' => $prizeId,
            ],
            ['total_poin' => 0]
        );
    }

    public function addPoints(int $points): int
    {
        $this->increment('total_poin', $points);

        return $this->total_poin;
    }
}
