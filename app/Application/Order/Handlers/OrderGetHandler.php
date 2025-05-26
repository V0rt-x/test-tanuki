<?php
declare(strict_types=1);

namespace App\Application\Order\Handlers;

use App\Application\Order\Commands\OrderGetCommand;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

class OrderGetHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    )
    {

    }

    /**
     * @param OrderGetCommand $command
     * @return Order
     * @throws OrderNotFoundException
     */
    public function handle(
        OrderGetCommand $command,
    ): Order
    {
        $orderId = $this->orderRepository->getWithCartAndDependencies($command->orderId);
        if (null === $orderId) {
            throw new OrderNotFoundException(sprintf('Order with id "%s" not found', $command->orderId));
        }

        return $orderId;
    }

}
