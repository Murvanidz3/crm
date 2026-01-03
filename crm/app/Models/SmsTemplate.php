<?php

namespace App\Models;

use App\Enums\CarStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'sms_templates';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'status_key',
        'template_text',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status_key' => CarStatus::class,
        ];
    }

    /**
     * Available placeholders for templates
     */
    const PLACEHOLDERS = [
        '[მანქანა]' => 'make_model',
        '[წელი]' => 'year',
        '[ვინ]' => 'vin',
        '[ლოტი]' => 'lot_number',
        '[კონტეინერი]' => 'container_number',
    ];

    /**
     * Compile template with car data
     */
    public function compile(Car $car): string
    {
        $message = $this->template_text;

        foreach (self::PLACEHOLDERS as $placeholder => $field) {
            $value = $car->{$field} ?: 'უცნობია';
            $message = str_replace($placeholder, $value, $message);
        }

        return $message;
    }

    /**
     * Get template for status
     */
    public static function getForStatus(CarStatus $status): ?self
    {
        return self::where('status_key', $status->value)->first();
    }
}
