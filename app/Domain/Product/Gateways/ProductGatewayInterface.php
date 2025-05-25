<?php
declare(strict_types=1);

namespace App\Domain\Product\Gateways;

use App\Domain\Product\Models\Product;

interface ProductGatewayInterface
{
    public function get(int $id): ?Product;
}
