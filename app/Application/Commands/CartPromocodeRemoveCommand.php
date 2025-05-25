<?php
declare(strict_types=1);

namespace App\Application\Commands;

class CartPromocodeRemoveCommand
{
    public function __construct(
        public int $cartId,
        public string $promocode,
    )
    {

    }
}
