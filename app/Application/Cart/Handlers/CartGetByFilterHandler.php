<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartGetByFilterCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Repositories\CartRepositoryInterface;

class CartGetByFilterHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    )
    {

    }

    /**
     * @param CartGetByFilterCommand $command
     * @return Cart
     * @throws CartNotFoundException
     */
    public function handle(CartGetByFilterCommand $command): Cart
    {
        $cart = $this->cartRepository->getUnorderedByUserId($command->userId, ['cartProducts', 'promocode.discount']);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart for user "%s" not found.', $command->userId));
        }

        return $cart;
    }
}
