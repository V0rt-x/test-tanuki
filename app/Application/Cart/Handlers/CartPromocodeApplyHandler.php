<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartPromocodeApplyCommand;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\PromocodeAlreadyUsedException;
use App\Domain\Cart\Exceptions\PromocodeNotFoundException;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Cart\Services\DiscountCalculatorService;
use App\Domain\Discount\Repositories\PromocodeRepositoryInterface;

class CartPromocodeApplyHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private PromocodeRepositoryInterface $promocodeRepository,
        private DiscountCalculatorService $discountCalculatorService,
    )
    {

    }

    /**
     * @param CartPromocodeApplyCommand $command
     * @throws CartNotFoundException
     * @throws PromocodeNotFoundException
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     * @throws PromocodeAlreadyUsedException
     */
    public function handle(CartPromocodeApplyCommand $command): void
    {
        $promocode = $this->promocodeRepository->getByCodeWithDiscount($command->promocode);
        if (null === $promocode) {
            throw new PromocodeNotFoundException(sprintf('Promocode "%s" not found', $command->promocode));
        }

        $cart = $this->cartRepository->getUnordered($command->cartId, ['cartProducts', 'promocode.discount']);
        if (null === $cart) {
            throw new CartNotFoundException(sprintf('Cart "%s" not found', $command->cartId));
        }

        if ($cart->hasPromocode()) {
            throw new PromocodeAlreadyUsedException('Promocode is already applied.');
        }

        $cart->setPromocode($promocode);

        if ($cart->isChanged()) {
            $this->discountCalculatorService->calculateCart($cart);
            $this->cartRepository->save($cart);
        }
    }
}
