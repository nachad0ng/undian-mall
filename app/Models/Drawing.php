<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drawing extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_period_id',
        'prize_id',
        'executed_by',
        'executed_at',
        'status',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'metadata' => 'array',
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

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }
}
