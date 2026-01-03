<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class CarFile extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'car_files';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'car_id',
        'file_path',
        'file_type',
        'category',
    ];

    /**
     * File type constants
     */
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_DOCUMENT = 'document';

    /**
     * Category constants
     */
    const CATEGORY_AUCTION = 'auction';
    const CATEGORY_PORT = 'port';
    const CATEGORY_TERMINAL = 'terminal';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The car this file belongs to
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get full URL to the file
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn() => asset($this->file_path)
        );
    }

    /**
     * Check if this is an image
     */
    protected function isImage(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->file_type === self::TYPE_IMAGE
        );
    }

    /**
     * Check if this is a video
     */
    protected function isVideo(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->file_type === self::TYPE_VIDEO
        );
    }

    /**
     * Get file extension
     */
    protected function extension(): Attribute
    {
        return Attribute::make(
            get: fn() => strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION))
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Delete the physical file
     */
    public function deleteFile(): bool
    {
        $path = public_path($this->file_path);
        
        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    /**
     * Delete model and physical file
     */
    public function deleteWithFile(): bool
    {
        $this->deleteFile();
        return $this->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for images only
     */
    public function scopeImages($query)
    {
        return $query->where('file_type', self::TYPE_IMAGE);
    }

    /**
     * Scope for videos only
     */
    public function scopeVideos($query)
    {
        return $query->where('file_type', self::TYPE_VIDEO);
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get all valid categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_AUCTION => 'აუქციონის ფოტოები',
            self::CATEGORY_PORT => 'პორტის ფოტოები',
            self::CATEGORY_TERMINAL => 'ფოთი / ტერმინალი',
        ];
    }

    /**
     * Get all valid file types
     */
    public static function getFileTypes(): array
    {
        return [
            self::TYPE_IMAGE => 'სურათი',
            self::TYPE_VIDEO => 'ვიდეო',
            self::TYPE_DOCUMENT => 'დოკუმენტი',
        ];
    }
}
