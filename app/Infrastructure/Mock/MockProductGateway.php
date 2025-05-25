<?php
declare(strict_types=1);

namespace App\Infrastructure\Mock;

use App\Domain\Product\Gateways\ProductGatewayInterface;
use App\Domain\Product\Models\Product;

class MockProductGateway implements ProductGatewayInterface
{
    public function get(int $id): ?Product
    {
        foreach ($this->products() as $product) {
            if ($product->getId() === $id) {
                return $product;
            }
        }

        return null;
    }

    private function products(): array
    {
        return [
            new Product(1, 10000),
            new Product(2, 12332),
            new Product(3, 100211),
            new Product(4, 56055),
            new Product(5, 80332),
            new Product(6, 63240),
            new Product(7, 9320011),
            new Product(8, 187263),
        ];
    }
}
