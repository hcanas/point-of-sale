<?php

namespace App\Enums;

enum MemberLedgerType: string
{
    case Purchase = 'purchase';
    case Payment = 'payment';
    case Adjustment = 'adjustment';
    case Initial = 'initial';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Payment => 'Payment',
            self::Adjustment => 'Adjustment',
            self::Initial => 'Initial Balance',
        };
    }
}
