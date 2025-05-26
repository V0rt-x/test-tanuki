<?php
declare(strict_types=1);

namespace App\Application\Order\Handlers;

use App\Application\Order\Commands\OrderCreateCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Order\Exceptions\CartCannotBeOrderedException;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

class OrderCreateHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CartRepositoryInterface  $cartRepository,
    )
    {

    }

    /**
     * @param OrderCreateCommand $command
     * @throws CartCannotBeOrderedException
     * @throws CartNotFoundException
     */
    public function handle(OrderCreateCommand $command): void
    {
        $cart = $this->cartRepository->unorderedWithProductsAndPromocodeByUserId($command->userId);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart for user "%s" not found.', $command->userId));
        }

        if (!$cart->canBeOrdered()) {
            throw new CartCannotBeOrderedException(sprintf('Cart %s for user "%s" cannot be ordered: min total final price unreached.', $cart->getId(), $command->userId));
        }

        $this->orderRepository->create(new Order(
            $command->phone,
            null,
            $cart->getId(),
            $cart
        ));
    }
}
