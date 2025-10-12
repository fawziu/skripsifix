<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'label',
        'recipient_name',
        'phone',
        'province_id',
        'city_id',
        'district_id',
        'postal_code',
        'address_line',
        'latitude',
        'longitude',
        'accuracy',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
    ];

    /**
     * Get the user that owns the address
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the city
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the district
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get full address string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->address_line,
            $this->district?->name,
            $this->city?->name,
            $this->province?->name,
            $this->postal_code
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Get address type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'home' => 'Rumah',
            'office' => 'Kantor',
            'warehouse' => 'Gudang',
            'other' => 'Lainnya',
            default => ucfirst($this->type)
        };
    }

    /**
     * Scope for active addresses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for primary addresses
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
