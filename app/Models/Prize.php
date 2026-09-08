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
        'quantity',
        'sequence',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'sequence' => 'integer',
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

    /**
     * Memeriksa apakah hadiah sudah digunakan dalam pengundian atau sudah memiliki pemenang.
     */
    public function isUsedInDrawing(): bool
    {
        return $this->drawings()->exists() || $this->winners()->exists();
    }
}
