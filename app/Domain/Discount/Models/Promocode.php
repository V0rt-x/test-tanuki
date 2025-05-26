<?php
declare(strict_types=1);

namespace App\Domain\Discount\Models;

use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Models\Cart;

class Promocode
{
    public function __construct(
        private string   $code,
        private int      $discountId,
        private ?int     $id = null,
        private ?Discount $discount = null,
    )
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDiscountId(): int
    {
        return $this->discountId;
    }

    /**
     * @return Discount|null
     * @throws DependencyNotLoadedException
     */
    public function getDiscount(): ?Discount
    {
        if ($this->discountId !== null && $this->discount === null) {
            throw new DependencyNotLoadedException('Discount not loaded for promocode.');
        }

        return $this->discount;
    }

    /**
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     */
    public function applyToCart(Cart $cart): void
    {
        $this->getDiscount()->applyToCart($cart);
    }
}
