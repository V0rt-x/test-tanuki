<?php
declare(strict_types=1);

namespace App\Application\Commands;

readonly class CartPromocodeApplyCommand
{
    public function __construct(
        public int $cartId,
        public string $promocode,
    )
    {

    }
}
