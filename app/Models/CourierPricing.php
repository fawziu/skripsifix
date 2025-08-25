<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierPricing extends Model
{
    use HasFactory;

    protected $table = 'courier_pricing';

    protected $fillable = [
        'courier_id',
        'base_fee',
        'per_kg_fee',
        'is_active',
        'bank_info',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'per_kg_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'bank_info' => 'array',
    ];

    /**
     * Get the courier that owns this pricing
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Calculate delivery fee based on weight
     */
    public function calculateFee(float $weight): float
    {
        return $this->base_fee + ($this->per_kg_fee * $weight);
    }

    /**
     * Scope to get only active pricing
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get pricing for active couriers
     */
    public function scopeForActiveCouriers($query)
    {
        return $query->whereHas('courier', function ($q) {
            $q->where('is_active', true);
        });
    }
}
