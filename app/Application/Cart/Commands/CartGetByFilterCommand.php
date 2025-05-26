<?php
declare(strict_types=1);

namespace App\Application\Cart\Commands;

readonly class CartGetByFilterCommand
{
    public function __construct(
        public int $userId,
    )
    {

    }
}
