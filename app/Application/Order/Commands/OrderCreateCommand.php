<?php
declare(strict_types=1);

namespace App\Application\Order\Commands;

use App\Domain\Shared\Models\ValueObjects\Phone;

readonly class OrderCreateCommand
{
    public function __construct(
        public int $userId,
        public Phone $phone,
    )
    {

    }
}
