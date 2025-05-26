<?php
declare(strict_types=1);

namespace App\Application\Discount\Mappers;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;
use App\Domain\Discount\Models\Discount;
use App\Infrastructure\Eloquent\Models\Discount as EloquentDiscount;

class DiscountMapper
{
    /**
     * @param EloquentDiscount $eloquentDiscount
     * @return Discount
     * @throws ExcessiveDiscountValueException
     */
    public static function fromEloquent(EloquentDiscount $eloquentDiscount): Discount
    {
        return new Discount(
            $eloquentDiscount->threshold,
            DiscountType::tryFrom($eloquentDiscount->type),
            $eloquentDiscount->value,
            $eloquentDiscount->id,
        );
    }
}
