<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartProductStoreCommand;
use App\Application\Cart\Commands\CartProductUpdateCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\CartProductsCapacityExceededException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\ProductNotFoundException;
use App\Domain\Cart\Exceptions\ProductNotInCartException;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Cart\Services\DiscountCalculatorService;
use App\Domain\Product\Gateways\ProductGatewayInterface;

class CartProductUpdateHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private DiscountCalculatorService $discountCalculatorService,
    )
    {
    }

    /**
     * @param CartProductUpdateCommand $command
     * @throws CartNotFoundException
     * @throws DependencyNotLoadedException
     * @throws DiscountInapplicableException
     * @throws ProductNotInCartException
     */
    public function handle(CartProductUpdateCommand $command): void
    {
        $cart = $this->cartRepository->unorderedWithProductsAndPromocode($command->cartId);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart with id: "%s" not found.', $command->cartId));
        }

        if ($cartProduct = $cart->getProduct($command->productId)) {
            $cart->updateProduct($cartProduct->setQuantity($command->quantity));
        } else {
            throw new ProductNotInCartException(sprintf('Product with id: "%s" not found in cart "%s".', $command->productId, $command->cartId));
        }

        if ($cart->isChanged()) {
            $cart = $this->discountCalculatorService->calculateCart($cart);
            $this->cartRepository->save($cart);
        }
    }
}
