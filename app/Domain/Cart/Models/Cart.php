<?php
declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Exceptions\CartProductsCapacityExceededException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Discount\Models\Discount;
use App\Domain\Discount\Models\Promocode;

class Cart
{
    const MAX_PRODUCT_CAPACITY = 30; // TODO config

    private bool $isChanged = false;

    /**
     * @param int|null $id
     * @param array<CartProduct> $cartProducts
     * @param int|null $promocodeId
     * @param Promocode|null $promocode
     */
    public function __construct(
        private ?int       $id = null,
        private array      $cartProducts = [],
        private ?int       $promocodeId = null,
        private ?Promocode $promocode = null,
    )
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array<CartProduct>
     */
    public function getCartProducts(): array
    {
        return $this->cartProducts;
    }

    /**
     * @return Promocode|null
     * @throws DependencyNotLoadedException
     */
    public function getPromocode(): ?Promocode
    {
        if ($this->promocodeId !== null && $this->promocode === null) {
            throw new DependencyNotLoadedException('Promocode not loaded for cart.');
        }

        return $this->promocode;
    }

    public function hasProduct(int $productId): bool
    {
        foreach ($this->cartProducts as $cartProduct) {
            if ($cartProduct->getProductId() === $productId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CartProduct $newCartProduct
     * @throws CartProductsCapacityExceededException
     */
    public function addProduct(CartProduct $newCartProduct): void
    {
        $this->changed();

        foreach ($this->cartProducts as $cartProduct) {
            if ($cartProduct->getProductId() === $newCartProduct->getProductId()) {
                $cartProduct->setQuantity($cartProduct->getQuantity() + $newCartProduct->getQuantity());
                return;
            }
        }

        if (count($this->cartProducts) >= self::MAX_PRODUCT_CAPACITY) {
            throw new CartProductsCapacityExceededException(sprintf('Cart products max capacity "%s" exceeded.', self::MAX_PRODUCT_CAPACITY));
        }

        $this->cartProducts[] = $newCartProduct->setCartId($this->id);
    }

    public function removeProduct(int $productId): void
    {
        $cartProducts = [];

        foreach ($this->cartProducts as $cartProduct) {
            if ($cartProduct->getProductId() !== $productId) {
                $cartProducts[] = $cartProduct;
            } else {
                $this->changed();
            }
        }

        $this->cartProducts = $cartProducts;
    }

    /**
     * Проверяет, привязан ли к корзине промокод
     * @return bool
     */
    public function hasPromocode(): bool
    {
        return null !== $this->promocodeId;
    }

    /**
     * Добавляет промокод для применения. Чтобы применить, нужно вызвать applyPromocode()
     * @param Promocode $promocode
     * @return void
     */
    public function setPromocode(Promocode $promocode): void
    {
        $this->changed();

        $this->promocodeId = $promocode->getId();
        $this->promocode = $promocode;
    }

    public function removePromocode(): void
    {
        $this->changed();

        $this->promocodeId = null;
        $this->promocode = null;
    }

    public function totalBaseSum(): int
    {
        return array_reduce($this->cartProducts, function ($carry, CartProduct $cartProduct) {
            return $carry + $cartProduct->getBasePrice() * $cartProduct->getQuantity();
        }, 0);
    }

    public function totalFinalSum(): int
    {
        return array_reduce($this->cartProducts, function ($carry, CartProduct $cartProduct) {
            return $carry + $cartProduct->getFinalPrice() * $cartProduct->getQuantity();
        }, 0);
    }

    public function totalQuantity(): int
    {
        return array_reduce($this->cartProducts, function ($carry, CartProduct $cartProduct) {
            return $carry + $cartProduct->getQuantity();
        }, 0);
    }

    /**
     * @return bool
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     */
    public function applyPromocode(): bool
    {
        $promocode = $this->getPromocode();
        if (null === $promocode) {
            return false;
        }

        if (null === $promocode->getDiscount()) {
            throw new DiscountInapplicableException(sprintf('Discount cannot be loaded for promocode "%s".', $promocode->getCode()));
        }

        $this->applyDiscount($promocode->getDiscount());

        return true;
    }

    /**
     * @param Discount $discount
     * @return void
     * @throws DiscountInapplicableException
     */
    public function applyDiscount(Discount $discount): void
    {
        if ($discount->getType() === DiscountType::ABSOLUTE) {
            $discountPerCartProduct = (int)($discount->getValue() / $this->totalQuantity());

            foreach ($this->cartProducts as $cartProduct) {
                $cartProduct->applyAbsoluteDiscount($discountPerCartProduct);
            }
        } elseif ($discount->getType() === DiscountType::PERCENT) {
            $discountPercent = $discount->getValue();

            foreach ($this->cartProducts as $cartProduct) {
                $cartProduct->applyPercentDiscount($discountPercent);
            }
        } else {
            throw new DiscountInapplicableException(sprintf('Discount type "%s" is not valid.', $discount->getType()->value));
        }
    }

    /**
     * Отменяет все примененные скидки
     * @return void
     */
    public function resetDiscounts(): void
    {
        foreach ($this->cartProducts as $cartProduct) {
            $cartProduct->resetDiscount();
        }
    }

    public function isChanged(): bool
    {
        return $this->isChanged;
    }

    private function changed(): void
    {
        $this->isChanged = true;
    }

    public function getPromocodeId(): ?int
    {
        return $this->promocodeId;
    }
}
