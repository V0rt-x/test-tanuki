<?php
declare(strict_types=1);

namespace App\Application\Commands;

readonly class CartGetCommand
{
    public function __construct(
        public int $cartId,
    )
    {

    }
}
