<?php
declare(strict_types=1);

namespace App\Application\Order\Commands;

readonly class OrderGetCommand
{
    public function __construct(
        public int $orderId,
    )
    {

    }
}
