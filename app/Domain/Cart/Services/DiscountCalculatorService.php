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
     * @return bool
     * @throws DependencyNotLoadedException
     * @throws DiscountInapplicableException
     */
    public function calculateCart(Cart $cart): bool
    {
        $cart->resetCartProductsDiscount();

        $promocode = $cart->getPromocode();
        if (null !== $promocode && !$promocode->isApplicableToCart($cart)) {
            $cart->removePromocode();
        }

        if ($this->applyPromocodeToCart($cart)) {
            return true;
        }

        return $this->applyDiscountToCart($cart);
    }

    /**
     * @param Cart $cart
     * @return bool
     * @throws DependencyNotLoadedException
     * @throws DiscountInapplicableException
     */
    private function applyPromocodeToCart(Cart $cart): bool
    {
        return $cart->applyPromocode();
    }

    /**
     * @param Cart $cart
     * @return bool
     * @throws DependencyNotLoadedException
     * @throws DiscountInapplicableException
     */
    private function applyDiscountToCart(Cart $cart): bool
    {
        $discount = $this->discountRepository->getGreatestApplicableWithoutPromocodes($cart->getTotalBaseSum());
        if (null == $discount || !$discount->isApplicableToCart($cart)) {
            return false;
        }

        $cart->setDiscount($discount);
        return $cart->applyDiscount();
    }
}
