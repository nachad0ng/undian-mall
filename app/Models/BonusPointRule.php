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
        'mode',
        'bonus_poin',
        'multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bonus_poin' => 'integer',
            'multiplier' => 'decimal:2',
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

    public function isMultiply(): bool
    {
        return $this->mode === 'multiply';
    }

    /**
     * Terapkan rule ke poin nominal. Kembalikan [total, bonus].
     * add: total = base + bonus_poin. multiply: total = base * multiplier.
     *
     * @return array{total: int, bonus: int}
     */
    public function applyToBasePoints(int $basePoints): array
    {
        if ($this->isMultiply()) {
            $total = (int) floor($basePoints * (float) $this->multiplier);

            return ['total' => $total, 'bonus' => $total - $basePoints];
        }

        return ['total' => $basePoints + $this->bonus_poin, 'bonus' => $this->bonus_poin];
    }

    public function describe(): string
    {
        if ($this->isMultiply()) {
            return rtrim(rtrim((string) $this->multiplier, '0'), '.').'x lipat';
        }

        return '+'.$this->bonus_poin.' poin';
    }
}
