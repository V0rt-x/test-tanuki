<?php
declare(strict_types=1);

namespace App\Application\Cart\Handlers;

use App\Application\Cart\Commands\CartCreateCommand;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Repositories\CartRepositoryInterface;

class CartCreateHandler
{
    public function __construct(private CartRepositoryInterface $cartRepository)
    {

    }

    public function handle(CartCreateCommand $command): void
    {
        $this->cartRepository->createEmpty(new Cart($command->userId));
    }
}
