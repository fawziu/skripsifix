<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'courier_id',
        'admin_id',
        'item_description',
        'item_weight',
        'item_price',
        'service_fee',
        'shipping_cost',
        'total_amount',
        'shipping_method',
        'origin_address',
        'destination_address',
        'origin_city',
        'destination_city',
        'courier_service',
        'tracking_number',
        'status',
        'rajaongkir_response',
        'estimated_delivery',
        'metadata',
    ];

    protected $casts = [
        'rajaongkir_response' => 'array',
        'estimated_delivery' => 'datetime',
        'metadata' => 'array',
        'item_weight' => 'decimal:2',
        'item_price' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the customer who placed the order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the courier assigned to the order
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get the admin who processed the order
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get order status history
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatus::class);
    }

    /**
     * Get complaints related to this order
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Get the origin city
     */
    public function originCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_city');
    }

    /**
     * Get the destination city
     */
    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city');
    }

    /**
     * Get the origin province
     */
    public function originProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'origin_province');
    }

    /**
     * Get the destination province
     */
    public function destinationProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'destination_province');
    }

    /**
     * Check if order uses manual shipping method
     */
    public function isManualShipping(): bool
    {
        return $this->shipping_method === 'manual';
    }

    /**
     * Check if order uses RajaOngkir shipping method
     */
    public function isRajaOngkirShipping(): bool
    {
        return $this->shipping_method === 'rajaongkir';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if order is assigned to courier
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Check if order is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'JST';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return "{$prefix}{$date}{$random}";
    }
}
