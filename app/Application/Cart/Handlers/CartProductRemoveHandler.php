<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartProductRemoveCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\ProductNotFoundException;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Cart\Services\DiscountCalculatorService;

class CartProductRemoveHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private DiscountCalculatorService $discountCalculatorService,
    )
    {

    }

    /**
     * @param CartProductRemoveCommand $command
     * @throws CartNotFoundException
     * @throws ProductNotFoundException
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     */
    public function handle(CartProductRemoveCommand $command): void
    {
        $cart = $this->cartRepository->getUnordered($command->cartId, ['cartProducts', 'promocode.discount']);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart with id: "%s" not found.', $command->cartId));
        }

        if ($cart->hasProduct($command->productId)) {
            $cart->removeProduct($command->productId);
        } else {
            throw new ProductNotFoundException(sprintf('Cart product with id "%s" not found in cart "%s".', $command->productId, $command->cartId));
        }

        if ($cart->isChanged()) {
            $this->discountCalculatorService->calculateCart($cart);
            $this->cartRepository->save($cart);
        }
    }
}
