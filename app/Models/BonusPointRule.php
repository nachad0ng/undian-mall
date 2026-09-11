<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusPointRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_period_id',
        'payment_type_id',
        'bonus_poin',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bonus_poin' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function rafflePeriod(): BelongsTo
    {
        return $this->belongsTo(RafflePeriod::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public static function findActiveForPeriodAndPaymentType(int $periodId, int $paymentTypeId): ?self
    {
        return static::where('raffle_period_id', $periodId)
            ->where('payment_type_id', $paymentTypeId)
            ->where('is_active', true)
            ->first();
    }
}
