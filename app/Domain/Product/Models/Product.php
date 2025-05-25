<?php
declare(strict_types=1);

namespace App\Domain\Product\Models;

class Product
{
    public function __construct(
        private int $id,
        private int $price,
    )
    {

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrice(): int
    {
        return $this->price;
    }
}
