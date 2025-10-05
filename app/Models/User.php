<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\CourierPricing;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user's role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get orders where user is customer
     */
    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Get orders where user is courier
     */
    public function courierOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    /**
     * Get orders where user is admin
     */
    public function adminOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'admin_id');
    }

    /**
     * Get complaints submitted by user
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Get complaints assigned to user (for admins)
     */
    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    /**
     * Get order status updates by user
     */
    public function orderStatusUpdates(): HasMany
    {
        return $this->hasMany(OrderStatus::class, 'updated_by');
    }

    /**
     * Get user addresses
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get primary address
     */
    public function primaryAddress(): HasMany
    {
        return $this->hasMany(Address::class)->where('is_primary', true);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->isAdmin();
    }

    /**
     * Check if user is courier
     */
    public function isCourier(): bool
    {
        return $this->role && $this->role->isCourier();
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role && $this->role->isCustomer();
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get courier pricing (for couriers only)
     */
    public function courierPricing(): HasOne
    {
        return $this->hasOne(CourierPricing::class, 'courier_id');
    }

    /**
     * Get available active couriers with pricing
     */
    public static function getActiveCouriersWithPricing()
    {
        return self::whereHas('role', function ($query) {
            $query->where('name', 'courier');
        })
        ->where('is_active', true)
        ->whereHas('courierPricing', function ($query) {
            $query->where('is_active', true);
        })
        ->with('courierPricing')
        ->get();
    }

    /**
     * Get location tracking for this user
     */
    public function locationTracking(): HasMany
    {
        return $this->hasMany(LocationTracking::class);
    }
}
