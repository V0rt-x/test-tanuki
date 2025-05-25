<?php
declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\Discount\Models\Discount;

class CartProduct
{
    public function __construct(
        private int $productId,
        private int $quantity,
        private int $basePrice,
        private ?int $cartId = null,
        private ?int $finalPrice = null,
        private ?int $id = null,
    )
    {

    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setCartId(?int $cartId): self
    {
        $this->cartId = $cartId;

        return $this;
    }

    public function getBasePrice(): int
    {
        return $this->basePrice;
    }

    public function getCartId(): ?int
    {
        return $this->cartId;
    }

    public function getFinalPrice(): ?int
    {
        return $this->finalPrice;
    }

    public function applyPercentDiscount(int $discountPercent): void
    {
        $this->finalPrice = $this->basePrice - (int)floor($this->basePrice * $discountPercent / 100);
    }

    public function applyAbsoluteDiscount(int $discountValue): void
    {
        $this->finalPrice = $this->basePrice - $discountValue;
    }

    public function resetDiscount(): void
    {
        $this->finalPrice = $this->basePrice;
    }
}
