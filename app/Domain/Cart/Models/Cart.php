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
    const MIN_TOTAL_FINAL_PRICE = 100000; // TODO config

    private bool $isChanged = false;

    /**
     * @param int $user_id
     * @param int|null $id
     * @param int|null $orderId
     * @param array<CartProduct> $cartProducts
     * @param int|null $discountId
     * @param int|null $promocodeId
     * @param Discount|null $discount
     * @param Promocode|null $promocode
     */
    public function __construct(
        private int        $user_id,
        private ?int       $id = null,
        private ?int       $orderId = null,
        private array      $cartProducts = [],
        private ?int       $discountId = null,
        private ?int       $promocodeId = null,
        private ?Discount  $discount = null,
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
     * @return Discount|null
     * @throws DependencyNotLoadedException
     */
    public function getDiscount(): ?Discount
    {
        if ($this->discountId !== null && $this->discount === null) {
            throw new DependencyNotLoadedException('Discount not loaded for cart.');
        }

        return $this->discount;
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

    public function getProduct(int $productId): ?CartProduct
    {
        foreach ($this->cartProducts as $cartProduct) {
            if ($cartProduct->getProductId() === $productId) {
                return clone $cartProduct;
            }
        }

        return null;
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

        if (count($this->cartProducts) + 1 >= self::MAX_PRODUCT_CAPACITY) {
            throw new CartProductsCapacityExceededException(sprintf('Cart max product capacity "%s" reached.', self::MAX_PRODUCT_CAPACITY));
        }

        $this->cartProducts[] = $newCartProduct->setCartId($this->id);
    }

    /**
     * Обновляет данные о товаре по product_id
     * @param CartProduct $updatingCartProduct
     * @return void
     */
    public function updateProduct(CartProduct $updatingCartProduct): void
    {
        $this->changed();

        $updatingCartProduct->setCartId($this->id);

        $cartProducts = [];
        foreach ($this->cartProducts as $cartProduct) {
            if ($cartProduct->getProductId() === $updatingCartProduct->getProductId()) {
                $cartProducts[] = $updatingCartProduct;
            } else {
                $cartProducts[] = $cartProduct;
            }
        }

        $this->cartProducts = $cartProducts;
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
     * Проверяет, привязана ли к корзине скидка
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return null !== $this->discountId;
    }

    /**
     * Добавляет скидку для применения. Чтобы применить, нужно вызвать applyDiscount()
     * @param Discount $discount
     * @return void
     * @throws DiscountInapplicableException
     */
    public function setDiscount(Discount $discount): void
    {
        if (!$discount->isApplicableToCart($this)) {
            throw new DiscountInapplicableException(sprintf('Cart total sum (%s) must be more than threshold %s', $this->getTotalBaseSum(), $discount->getThreshold()));
        }

        $this->changed();

        $this->discountId = $discount->getId();
        $this->discount = $discount;
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
     * @throws DependencyNotLoadedException
     * @throws DiscountInapplicableException
     */
    public function setPromocode(Promocode $promocode): void
    {
        if (!$promocode->isApplicableToCart($this)) {
            throw new DiscountInapplicableException(sprintf('Cart total sum (%s) must be more than threshold %s', $this->getTotalBaseSum(), $promocode->getDiscount()->getThreshold()));
        }

        $this->changed();

        $this->promocodeId = $promocode->getId();
        $this->promocode = $promocode;
    }

    public function removeDiscount(): void
    {
        $this->changed();

        $this->discountId = null;
        $this->discount = null;
    }

    public function removePromocode(): void
    {
        $this->changed();

        $this->promocodeId = null;
        $this->promocode = null;
    }

    public function getTotalBaseSum(): int
    {
        return array_reduce($this->cartProducts, function ($carry, CartProduct $cartProduct) {
            return $carry + $cartProduct->getBasePrice() * $cartProduct->getQuantity();
        }, 0);
    }

    public function getTotalFinalSum(): int
    {
        return array_reduce($this->cartProducts, function ($carry, CartProduct $cartProduct) {
            return $carry + $cartProduct->getFinalPrice() * $cartProduct->getQuantity();
        }, 0);
    }

    public function getTotalQuantity(): int
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
        $this->removeDiscount();

        $promocode = $this->getPromocode();

        if (null === $promocode) {
            return false;
        }

        $promocode->applyToCart($this);

        return true;
    }

    /**
     * Применяет скидку к товарам в корзине
     * @return bool
     * @throws DependencyNotLoadedException
     * @throws DiscountInapplicableException
     */
    public function applyDiscount(): bool
    {
        $this->removePromocode();

        $discount = $this->getDiscount();

        if (null === $discount) {
            return false;
        }

        $discount->applyToCart($this);

        return true;
    }

    /**
     * Отменяет все примененные к товарам скидки
     * @return void
     */
    public function resetCartProductsDiscount(): void
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

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function isOrdered(): bool
    {
        return $this->orderId !== null;
    }

    public function canBeOrdered(): bool
    {
        $sum = $this->getTotalFinalSum() !== 0 ? $this->getTotalFinalSum() : $this->getTotalBaseSum();

        return !$this->isOrdered() && $sum >= self::MIN_TOTAL_FINAL_PRICE;
    }
}
