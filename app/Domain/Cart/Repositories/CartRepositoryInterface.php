<?php
declare(strict_types=1);

namespace App\Domain\Cart\Repositories;

use App\Domain\Cart\Models\Cart;

interface CartRepositoryInterface
{
    public function get(int $id): ?Cart;

    public function createEmpty(Cart $eloquentCart): Cart;

    public function save(Cart $cart): void;

    public function withProductsAndPromocode(int $id): ?Cart;
}
