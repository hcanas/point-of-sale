<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case INVENTORY = 'inventory';
    case AUDITOR = 'auditor';
    case CASHIER = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::INVENTORY => 'Inventory Staff',
            self::AUDITOR => 'Auditor',
            self::CASHIER => 'Cashier',
        };
    }
}
