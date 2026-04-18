<?php

namespace App\Enums;

enum CashMovementType: string
{
    case Sale = 'sale';
    case Purchase = 'purchase';
    case Payment = 'payment';
    case Refund = 'refund';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Sale',
            self::Purchase => 'Purchase',
            self::Payment => 'Payment',
            self::Refund => 'Refund',
            self::Adjustment => 'Adjustment',
        };
    }
}
