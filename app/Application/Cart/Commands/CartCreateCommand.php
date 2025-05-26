<?php
declare(strict_types=1);

namespace App\Application\Cart\Commands;

readonly class CartCreateCommand
{
    public function __construct(public int $userId)
    {

    }
}
