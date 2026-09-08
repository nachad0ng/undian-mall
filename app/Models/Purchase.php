<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'total_coupons',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'amount' => 'integer',
            'total_coupons' => 'integer',
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

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }
}
