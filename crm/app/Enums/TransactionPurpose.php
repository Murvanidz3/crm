<?php

namespace App\Enums;

enum TransactionPurpose: string
{
    case VEHICLE_PAYMENT = 'vehicle';
    case SHIPPING = 'shipping';
    case BALANCE_TOPUP = 'balance_topup';
    case INTERNAL_TRANSFER = 'internal_transfer';
    case OTHER = 'other';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::VEHICLE_PAYMENT => 'მანქანის საფასური',
            self::SHIPPING => 'ტრანსპორტირება',
            self::BALANCE_TOPUP => 'ბალანსის შევსება',
            self::INTERNAL_TRANSFER => 'შიდა გადარიცხვა',
            self::OTHER => 'სხვა',
        };
    }

    /**
     * Get badge CSS class
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::VEHICLE_PAYMENT => 'bg-primary',
            self::SHIPPING => 'bg-info',
            self::BALANCE_TOPUP => 'bg-success',
            self::INTERNAL_TRANSFER => 'bg-warning text-dark',
            self::OTHER => 'bg-secondary',
        };
    }

    /**
     * Is this a balance operation?
     */
    public function isBalanceOperation(): bool
    {
        return in_array($this, [self::BALANCE_TOPUP, self::INTERNAL_TRANSFER]);
    }

    /**
     * Get all purposes as array for forms
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
