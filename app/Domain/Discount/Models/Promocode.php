<?php
declare(strict_types=1);

namespace App\Domain\Discount\Models;

class Promocode
{
    private ?Discount $discount = null;

    public function __construct(
        private string $code,
        private int    $discountId,
        private ?int   $id = null,
    )
    {

    }

    public function setDiscount(?Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
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

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }
}
