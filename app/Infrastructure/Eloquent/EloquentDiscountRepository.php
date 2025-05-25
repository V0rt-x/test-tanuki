<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Discount\Models\Discount;
use App\Infrastructure\Eloquent\Models\Discount as EloquentDiscount;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;

class EloquentDiscountRepository implements DiscountRepositoryInterface
{
    public function getGreatestApplicableWithoutPromocodes(int $threshold): ?Discount
    {
        $eloquentDiscount = EloquentDiscount::doesntHave('promocodes')
            ->where('threshold', '<=', $threshold)
            ->orderBy('threshold', 'desc')
            ->first();

        return $eloquentDiscount ? $this->eloquentToDomain($eloquentDiscount) : null;
    }

    private function eloquentToDomain(EloquentDiscount $eloquentDiscount): Discount
    {
        return new Discount(
            $eloquentDiscount->threshold,
            DiscountType::tryFrom($eloquentDiscount->type),
            $eloquentDiscount->value,
            $eloquentDiscount->id,
        );
    }
}
