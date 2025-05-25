<?php
declare(strict_types=1);

namespace App\Domain\Discount\Models;

use App\Domain\Cart\Enums\DiscountType;

class Discount
{
    /**
     * @param int $threshold Порог (сумма корзины), от которого начинает действовать скидка
     * @param DiscountType $type
     * @param int $value
     * @param int|null $id
     */
    public function __construct(
        private int          $threshold,
        private DiscountType $type,
        private int          $value,
        private ?int         $id = null,
    )
    {

    }

    public function getType(): DiscountType
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
