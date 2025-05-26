<?php
declare(strict_types=1);

namespace App\Application\Order\Commands;

readonly class OrderCreateCommand
{
    public function __construct(
        public int $userId,
        public string $phone,
    )
    {

    }
}
