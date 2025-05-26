<?php
declare(strict_types=1);

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Models\Order;

interface OrderRepositoryInterface
{
    public function create(Order $order);

    public function get(int $orderId, array $with = []): ?Order;
}
