<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Sale = 'sale';
    case Restock = 'restock';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case Initial = 'initial';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Sale',
            self::Restock => 'Restock',
            self::Return => 'Return',
            self::Adjustment => 'Adjustment',
            self::Initial => 'Initial',
        };
    }
}
