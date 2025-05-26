<?php
declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Cart\Models\Cart;
use App\Domain\Shared\Models\ValueObjects\Phone;

class Order
{
    public function __construct(
        private Phone $phone,
        private ?int  $id = null,
        private ?int  $cartId = null,
        private ?Cart $cart = null,
    )
    {
    }

    public function getPhone(): Phone
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

    public function getId(): ?int
    {
        return $this->id;
    }
}
