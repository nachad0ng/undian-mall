<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'raffle_period_id',
        'customer_id',
        'tenant_id',
        'entered_by',
        'receipt_number',
        'purchased_at',
        'amount',
        'payment_type_id',
        'exchange_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function rafflePeriod(): BelongsTo
    {
        return $this->belongsTo(RafflePeriod::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function pointRedemption(): BelongsTo
    {
        return $this->belongsTo(PointRedemption::class);
    }

    public function isAlreadyRedeemed(): bool
    {
        return $this->exchange_status === 'sudah';
    }

    public function markAsRedeemed(): bool
    {
        return $this->update(['exchange_status' => 'sudah']);
    }

    public function scopeNotRedeemed($query)
    {
        return $query->where('exchange_status', 'belum');
    }

    public function scopeWithinExchangePeriod($query, CarbonInterface $start, CarbonInterface $end)
    {
        return $query->whereBetween('purchased_at', [$start, $end]);
    }
}
