<?php
declare(strict_types=1);

namespace App\Application\Cart\Commands;

readonly class CartGetCommand
{
    public function __construct(
        public int $cartId,
    )
    {

    }
}
