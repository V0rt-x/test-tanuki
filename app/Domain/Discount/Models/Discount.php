<?php
declare(strict_types=1);

namespace App\Domain\Discount\Models;

use App\Domain\Cart\Enums\DiscountType;
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
}
