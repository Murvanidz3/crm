<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DEALER = 'dealer';
    case CLIENT = 'client';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'ადმინისტრატორი',
            self::DEALER => 'დილერი',
            self::CLIENT => 'კლიენტი',
        };
    }

    /**
     * Get badge color class
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::ADMIN => 'bg-danger',
            self::DEALER => 'bg-info',
            self::CLIENT => 'bg-success',
        };
    }

    /**
     * Check if user can manage cars
     */
    public function canManageCars(): bool
    {
        return in_array($this, [self::ADMIN, self::DEALER]);
    }

    /**
     * Check if user has admin privileges
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Get all roles as array for forms
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
