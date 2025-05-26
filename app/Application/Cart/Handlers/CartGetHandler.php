<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartGetCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Repositories\CartRepositoryInterface;

class CartGetHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    )
    {

    }

    /**
     * @throws CartNotFoundException
     */
    public function handle(CartGetCommand $command): Cart
    {
        $cart = $this->cartRepository->getUnordered($command->cartId, ['cartProducts', 'promocode.discount', 'discount']);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart %s not found', $command->cartId));
        }

        return $cart;
    }
}
