<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartProductStoreCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\CartProductsCapacityExceededException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\ProductAlreadyInCartException;
use App\Domain\Cart\Exceptions\ProductNotFoundException;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Cart\Services\DiscountCalculatorService;
use App\Domain\Product\Gateways\ProductGatewayInterface;

class CartProductStoreHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private ProductGatewayInterface $productGateway,
        private DiscountCalculatorService $discountCalculatorService,
    )
    {
    }

    /**
     * @param CartProductStoreCommand $command
     * @throws CartNotFoundException
     * @throws ProductNotFoundException
     * @throws CartProductsCapacityExceededException
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     * @throws ProductAlreadyInCartException
     */
    public function handle(CartProductStoreCommand $command): void
    {
        $product = $this->productGateway->get($command->productId);
        if (null === $product) {
            throw new ProductNotFoundException(sprintf('Product with id: "%s" not found.', $command->productId));
        }

        $cart = $this->cartRepository->getUnordered($command->cartId, ['cartProducts', 'promocode.discount']);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart with id: "%s" not found.', $command->cartId));
        }

        if (!$cart->hasProduct($product->getId())) {
            $cart->addProduct(new CartProduct(
                $product->getId(),
                $command->quantity,
                $product->getPrice(),
            ));
        } else {
            throw new ProductAlreadyInCartException(sprintf('Product with id: "%s" already in cart "%s".', $command->productId, $command->cartId));
        }

        if ($cart->isChanged()) {
            $this->discountCalculatorService->calculateCart($cart);
            $this->cartRepository->save($cart);
        }
    }
}
