<?php
declare(strict_types=1);

namespace App\Domain\Cart\Repositories;

use App\Domain\Cart\Models\Cart;

interface CartRepositoryInterface
{
    public function getUnordered(int $id): ?Cart;

    public function createEmpty(Cart $cart): Cart;

    public function save(Cart $cart): void;

    public function unorderedWithProductsAndPromocode(int $id): ?Cart;

    public function unorderedWithProductsAndPromocodeByUserId(int $userId): ?Cart;
}
