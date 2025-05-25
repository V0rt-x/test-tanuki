<?php
declare(strict_types=1);

namespace App\Domain\Discount\Repositories;

use App\Domain\Discount\Models\Promocode;

interface PromocodeRepositoryInterface
{
    public function getByCode(string $code): ?Promocode;

    public function getByCodeWithDiscount(string $code): ?Promocode;
}
