<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'No pagado',
            self::Paid => 'Pagado',
            self::Refunded => 'Reembolsado',
            self::Failed => 'Pago fallido',
        };
    }
}
