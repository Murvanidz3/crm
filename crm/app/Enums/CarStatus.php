<?php

namespace App\Enums;

enum CarStatus: string
{
    case PURCHASED = 'purchased';
    case WAREHOUSE = 'warehouse';
    case LOADED = 'loaded';
    case ON_WAY = 'on_way';
    case POTI = 'poti';
    case GREEN = 'green';
    case DELIVERED = 'delivered';

    /**
     * Get human-readable label (Georgian)
     */
    public function label(): string
    {
        return match($this) {
            self::PURCHASED => 'შეძენილია',
            self::WAREHOUSE => 'საწყობშია',
            self::LOADED => 'ჩატვირთულია',
            self::ON_WAY => 'გზაშია',
            self::POTI => 'ფოთშია',
            self::GREEN => 'მწვანეშია',
            self::DELIVERED => 'გაყვანილია',
        };
    }

    /**
     * Get emoji icon
     */
    public function icon(): string
    {
        return match($this) {
            self::PURCHASED => '🟡',
            self::WAREHOUSE => '🟤',
            self::LOADED => '📦',
            self::ON_WAY => '🔵',
            self::POTI => '⚓',
            self::GREEN => '🟢',
            self::DELIVERED => '✅',
        };
    }

    /**
     * Get CSS class for badge styling
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::PURCHASED => 'status-purchased',
            self::WAREHOUSE => 'status-warehouse',
            self::LOADED => 'status-loaded',
            self::ON_WAY => 'status-on_way',
            self::POTI => 'status-poti',
            self::GREEN => 'status-green',
            self::DELIVERED => 'status-delivered',
        };
    }

    /**
     * Get workflow order (for progress tracking)
     */
    public function order(): int
    {
        return match($this) {
            self::PURCHASED => 1,
            self::WAREHOUSE => 2,
            self::LOADED => 3,
            self::ON_WAY => 4,
            self::POTI => 5,
            self::GREEN => 6,
            self::DELIVERED => 7,
        };
    }

    /**
     * Get progress percentage
     */
    public function progressPercent(): int
    {
        return (int) (($this->order() / 7) * 100);
    }

    /**
     * Get all statuses as array for forms
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->icon() . ' ' . $case->label(),
            'order' => $case->order(),
        ], self::cases());
    }

    /**
     * Get next status in workflow
     */
    public function next(): ?self
    {
        return match($this) {
            self::PURCHASED => self::WAREHOUSE,
            self::WAREHOUSE => self::LOADED,
            self::LOADED => self::ON_WAY,
            self::ON_WAY => self::POTI,
            self::POTI => self::GREEN,
            self::GREEN => self::DELIVERED,
            self::DELIVERED => null,
        };
    }
}
