<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;
use App\Domain\Discount\Models\Discount;
use App\Domain\Discount\Models\Promocode;
use App\Domain\Discount\Repositories\PromocodeRepositoryInterface;
use App\Infrastructure\Eloquent\Models\Promocode as EloquentPromocode;

class EloquentPromocodeRepository implements PromocodeRepositoryInterface
{

    public function getByCode(string $code): ?Promocode
    {
        $eloquentPromocode = EloquentPromocode::where('code', $code)->first();

        return $eloquentPromocode ? $this->eloquentToDomain($eloquentPromocode) : null;
    }

    public function getByCodeWithDiscount(string $code): ?Promocode
    {
        $eloquentPromocode = EloquentPromocode::with('discount')->where('code', $code)->first();

        return $eloquentPromocode ? $this->eloquentToDomain($eloquentPromocode) : null;
    }

    /**
     * @param EloquentPromocode $eloquentPromocode
     * @return Promocode
     * @throws ExcessiveDiscountValueException
     */
    private function eloquentToDomain(EloquentPromocode $eloquentPromocode): Promocode
    {
        if ($eloquentPromocode->relationLoaded('discount') && null !== $eloquentPromocode->discount) {
            $discount = new Discount(
                $eloquentPromocode->discount->threshold,
                DiscountType::tryFrom($eloquentPromocode->discount->type),
                $eloquentPromocode->discount->value,
                $eloquentPromocode->discount->id,
            );
        }

        return new Promocode(
            $eloquentPromocode->code,
            $eloquentPromocode->discount_id,
            $eloquentPromocode->id,
            $discount ?? null,
        );
    }
}
