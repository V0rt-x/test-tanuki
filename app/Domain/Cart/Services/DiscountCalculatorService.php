<?php
declare(strict_types=1);

namespace App\Domain\Cart\Services;

use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;

class DiscountCalculatorService
{
    public function __construct(
        private DiscountRepositoryInterface $discountRepository,
    )
    {

    }

    /**
     * @param Cart $cart
     * @return Cart
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     */
    public function calculateCart(Cart $cart): Cart
    {
        $cart->resetDiscounts();

        if ($cart->getPromocode()->isApplicable() && $cart->applyPromocode()) {
            return $cart;
        }

        $discount = $this->discountRepository->getGreatestApplicableWithoutPromocodes($cart->totalBaseSum());

        if ($discount) {
            $cart->applyDiscount($discount);

            return $cart;
        }

        return $cart;
    }
}
