<?php
declare(strict_types=1);

namespace App\Application\Commands;

readonly class CartProductRemoveCommand
{
    public function __construct(
        public int $cartId,
        public int $productId,
    )
    {

    }
}
