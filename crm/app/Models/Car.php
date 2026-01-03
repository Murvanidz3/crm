<?php

namespace App\Models;

use App\Enums\CarStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

class Car extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'client_user_id',
        'vin',
        'make_model',
        'year',
        'lot_number',
        'auction_name',
        'auction_location',
        'container_number',
        'status',
        'vehicle_cost',
        'shipping_cost',
        'additional_cost',
        'paid_amount',
        'purchase_date',
        'main_photo',
        'client_name',
        'client_phone',
        'client_id_number',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => CarStatus::class,
            'vehicle_cost' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'additional_cost' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'purchase_date' => 'date',
            'year' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Default values for attributes
     */
    protected $attributes = [
        'vehicle_cost' => 0,
        'shipping_cost' => 0,
        'additional_cost' => 0,
        'paid_amount' => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Owner of the car (dealer/admin)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Client/buyer of the car
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    /**
     * Files/photos associated with this car
     */
    public function files(): HasMany
    {
        return $this->hasMany(CarFile::class);
    }

    /**
     * Transactions for this car
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get total cost (vehicle + shipping + additional)
     */
    protected function totalCost(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->vehicle_cost + $this->shipping_cost + $this->additional_cost
        );
    }

    /**
     * Get transport total (shipping + additional)
     */
    protected function transportCost(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->shipping_cost + $this->additional_cost
        );
    }

    /**
     * Get remaining debt
     */
    protected function debt(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->total_cost - $this->paid_amount
        );
    }

    /**
     * Check if car is fully paid
     */
    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->debt <= 0
        );
    }

    /**
     * Get payment progress percentage
     */
    protected function paymentProgress(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->total_cost <= 0) return 100;
                return min(100, ($this->paid_amount / $this->total_cost) * 100);
            }
        );
    }

    /**
     * Get main photo URL or placeholder
     */
    protected function mainPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->main_photo && file_exists(public_path($this->main_photo))) {
                    return asset($this->main_photo);
                }
                return 'https://via.placeholder.com/300x200/333/999?text=No+Photo';
            }
        );
    }

    /**
     * Get status badge HTML
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => sprintf(
                '<span class="badge-soft %s">%s</span>',
                $this->status?->badgeClass() ?? 'bg-secondary',
                $this->status?->label() ?? 'უცნობია'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for owner filtering
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isDealer()) {
            return $query->where('user_id', $user->id);
        }

        if ($user->isClient()) {
            return $query->where('client_user_id', $user->id);
        }

        return $query->whereRaw('1 = 0'); // Return nothing
    }

    /**
     * Scope for status filtering
     */
    public function scopeWithStatus(Builder $query, CarStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for search
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function($q) use ($search) {
            $q->where('vin', 'LIKE', "%{$search}%")
              ->orWhere('make_model', 'LIKE', "%{$search}%")
              ->orWhere('lot_number', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope for cars with debt
     */
    public function scopeWithDebt(Builder $query): Builder
    {
        return $query->whereRaw('(vehicle_cost + shipping_cost + additional_cost - paid_amount) > 0');
    }

    /**
     * Scope for on-way cars
     */
    public function scopeOnWay(Builder $query): Builder
    {
        return $query->where('status', CarStatus::ON_WAY);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Add payment to this car
     */
    public function addPayment(float $amount): bool
    {
        return $this->increment('paid_amount', $amount);
    }

    /**
     * Update status and trigger SMS if needed
     */
    public function updateStatus(CarStatus $newStatus): bool
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $saved = $this->save();

        if ($saved && $oldStatus !== $newStatus) {
            event(new \App\Events\CarStatusChanged($this, $oldStatus, $newStatus));
        }

        return $saved;
    }

    /**
     * Get files by category
     */
    public function getFilesByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->where('category', $category)->orderBy('id', 'desc')->get();
    }

    /**
     * Set main photo from file
     */
    public function setMainPhoto(CarFile $file): bool
    {
        if ($file->car_id !== $this->id || $file->file_type !== 'image') {
            return false;
        }

        $this->main_photo = $file->file_path;
        return $this->save();
    }
}
