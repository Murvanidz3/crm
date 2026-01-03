<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'full_name',
        'phone',
        'password',
        'role',
        'balance',
        'sms_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'balance' => 'decimal:2',
            'sms_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Cars owned by this user (as dealer/admin)
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'user_id');
    }

    /**
     * Cars where this user is the client/buyer
     */
    public function clientCars(): HasMany
    {
        return $this->hasMany(Car::class, 'client_user_id');
    }

    /**
     * Transactions made by this user
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted balance
     */
    protected function formattedBalance(): Attribute
    {
        return Attribute::make(
            get: fn() => '$' . number_format($this->balance, 2)
        );
    }

    /**
     * Get display name (full_name or username)
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->full_name ?: $this->username
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user is dealer
     */
    public function isDealer(): bool
    {
        return $this->role === UserRole::DEALER;
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    /**
     * Check if user can edit a specific car
     */
    public function canEditCar(Car $car): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isDealer()) {
            return $car->user_id === $this->id;
        }

        return false;
    }

    /**
     * Check if user can view a specific car
     */
    public function canViewCar(Car $car): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isDealer()) {
            return $car->user_id === $this->id;
        }

        if ($this->isClient()) {
            return $car->client_user_id === $this->id;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to get dealers only
     */
    public function scopeDealers($query)
    {
        return $query->where('role', UserRole::DEALER);
    }

    /**
     * Scope to get clients only
     */
    public function scopeClients($query)
    {
        return $query->where('role', UserRole::CLIENT);
    }

    /**
     * Scope to get users with SMS enabled
     */
    public function scopeSmsEnabled($query)
    {
        return $query->where('sms_enabled', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Balance Operations
    |--------------------------------------------------------------------------
    */

    /**
     * Add amount to balance
     */
    public function addBalance(float $amount): bool
    {
        return $this->increment('balance', $amount);
    }

    /**
     * Deduct amount from balance (with validation)
     */
    public function deductBalance(float $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }

        return $this->decrement('balance', $amount);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}
