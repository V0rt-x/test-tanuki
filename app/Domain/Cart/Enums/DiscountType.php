<?php
declare(strict_types=1);

namespace App\Domain\Cart\Enums;

/**
 * ABSOLUTE - Абсолютная скидка, то есть, например, 1000 рублей
 *
 * PERCENT - Процентная скидка, то есть, например, 10% от суммы
 */
enum DiscountType: string
{
    case ABSOLUTE = 'abs';

    case PERCENT = 'percent';
}
