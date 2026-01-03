<?php

namespace App\Models;

use App\Enums\TransactionPurpose;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Disable default timestamps (we use payment_date)
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'car_id',
        'user_id',
        'amount',
        'payment_date',
        'purpose',
        'comment',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'purpose' => TransactionPurpose::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The car this transaction belongs to
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * The user who made/received this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted amount with sign
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: function() {
                $sign = $this->isIncome() ? '+' : '-';
                return $sign . '$' . number_format($this->amount, 2);
            }
        );
    }

    /**
     * Get amount color class
     */
    protected function amountClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->isIncome() ? 'text-success' : 'text-danger'
        );
    }

    /**
     * Check if this is an income transaction
     */
    public function isIncome(): bool
    {
        return $this->purpose === TransactionPurpose::BALANCE_TOPUP;
    }

    /**
     * Check if this is a car payment
     */
    public function isCarPayment(): bool
    {
        return $this->car_id !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to filter by user ownership
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('car', fn($cq) => $cq->where('user_id', $user->id));
        });
    }

    /**
     * Scope for balance transactions only
     */
    public function scopeBalanceOnly(Builder $query): Builder
    {
        return $query->whereNull('car_id');
    }

    /**
     * Scope for car payments only
     */
    public function scopeCarPaymentsOnly(Builder $query): Builder
    {
        return $query->whereNotNull('car_id');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('payment_date', [$start, $end]);
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new car payment transaction
     */
    public static function createCarPayment(
        Car $car,
        float $amount,
        TransactionPurpose $purpose,
        ?string $comment = null
    ): self {
        return self::create([
            'car_id' => $car->id,
            'user_id' => $car->user_id,
            'amount' => $amount,
            'payment_date' => now(),
            'purpose' => $purpose,
            'comment' => $comment,
        ]);
    }

    /**
     * Create a balance topup transaction
     */
    public static function createBalanceTopup(
        User $user,
        float $amount,
        ?string $comment = null
    ): self {
        return self::create([
            'car_id' => null,
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_date' => now(),
            'purpose' => TransactionPurpose::BALANCE_TOPUP,
            'comment' => $comment,
        ]);
    }
}
