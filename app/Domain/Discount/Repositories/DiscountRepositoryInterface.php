<?php
declare(strict_types=1);

namespace App\Domain\Discount\Repositories;

use App\Domain\Discount\Models\Discount;

interface DiscountRepositoryInterface
{
    /**
     * Получение скидки, не имеющей промокодов, с наибольшим порогом, не меньшим $threshold.
     * @param int $threshold
     * @return Discount|null
     */
    public function getGreatestApplicableWithoutPromocodes(int $threshold): ?Discount;
}
