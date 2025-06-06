<?php
declare(strict_types=1);

namespace App\Application\Cart\Commands;

readonly class CartProductStoreCommand
{
    public function __construct(
        public int $cartId,
        public int $productId,
        public int $quantity,
    )
    {

    }
}
