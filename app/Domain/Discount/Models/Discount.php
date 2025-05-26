<?php
declare(strict_types=1);

namespace App\Domain\Discount\Models;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;

class Discount
{
    /**
     * @param int $threshold Порог (сумма корзины), от которого начинает действовать скидка
     * @param DiscountType $type
     * @param int $value
     * @param int|null $id
     * @throws ExcessiveDiscountValueException
     */
    public function __construct(
        private int          $threshold,
        private DiscountType $type,
        private int          $value,
        private ?int         $id = null,
    )
    {
        if (
            $this->type === DiscountType::PERCENT
            && $this->value >= 100
        ) {
            throw new ExcessiveDiscountValueException(sprintf('Percent discount value cannot be more than 100%% (%s)', $this->value));
        }

    }

    public function getType(): DiscountType
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isApplicableToCart(Cart $cart): bool
    {
        return $cart->getTotalBaseSum() >= $this->threshold;
    }

    /**
     * @param Cart $cart
     * @throws DiscountInapplicableException
     */
    public function applyToCart(Cart $cart): void
    {
        if (!$this->isApplicableToCart($cart)) {
            throw new DiscountInapplicableException(sprintf('Cart total sum (%s) must be more than threshold %s', $cart->getTotalBaseSum(), $this->threshold));
        }

        if ($this->getType() === DiscountType::ABSOLUTE) {
            $discountPerCartProduct = (int)($this->getValue() / $cart->getTotalQuantity());

            foreach ($cart->getCartProducts() as $cartProduct) {
                $cartProduct->applyAbsoluteDiscount($discountPerCartProduct);
            }
        } elseif ($this->getType() === DiscountType::PERCENT) {
            $discountPercent = $this->getValue();

            foreach ($cart->getCartProducts() as $cartProduct) {
                $cartProduct->applyPercentDiscount($discountPercent);
            }
        } else {
            throw new DiscountInapplicableException(sprintf('Discount type "%s" is not valid.', $this->getType()->value));
        }
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }
}
