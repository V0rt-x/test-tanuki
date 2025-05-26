<?php
declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Cart\Models\Cart;

class Order
{
    public function __construct(
        private string $phone,
        private ?int   $id = null,
        private ?int   $cartId = null,
        private ?Cart  $cart = null,
    )
    {
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }
}
