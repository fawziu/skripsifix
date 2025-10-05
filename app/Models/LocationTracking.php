<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'user_type',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'tracked_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'tracked_at' => 'datetime',
    ];

    /**
     * Get the order that owns the location tracking
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that owns the location tracking
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for courier locations
     */
    public function scopeCourier($query)
    {
        return $query->where('user_type', 'courier');
    }

    /**
     * Scope for customer locations
     */
    public function scopeCustomer($query)
    {
        return $query->where('user_type', 'customer');
    }

    /**
     * Scope for recent locations (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('tracked_at', '>=', now()->subDay());
    }
}
