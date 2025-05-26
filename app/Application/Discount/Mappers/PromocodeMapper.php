<?php
declare(strict_types=1);

namespace App\Application\Discount\Mappers;

use App\Domain\Discount\Models\Promocode;
use App\Infrastructure\Eloquent\Models\Promocode as EloquentPromocode;

class PromocodeMapper
{
    /**
     * @param EloquentPromocode $eloquentPromocode
     * @return Promocode
     */
    public static function fromEloquent(EloquentPromocode $eloquentPromocode): Promocode
    {
        if ($eloquentPromocode->relationLoaded('discount') && null !== $eloquentPromocode->discount) {
            $discount = DiscountMapper::fromEloquent($eloquentPromocode->discount);
        }

        return new Promocode(
            $eloquentPromocode->code,
            $eloquentPromocode->discount_id,
            $eloquentPromocode->id,
            $discount ?? null,
        );
    }
}
