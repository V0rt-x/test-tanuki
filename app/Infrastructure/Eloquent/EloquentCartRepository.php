<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

use App\Application\Cart\Mappers\CartMapper;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Models\Promocode;
use App\Infrastructure\Eloquent\Models\Cart as EloquentCart;
use Illuminate\Support\Facades\DB;

class EloquentCartRepository implements CartRepositoryInterface
{
    /**
     * Получение корзины без зависимостей
     * @param int $id
     * @return Cart|null
     */
    public function getUnordered(int $id): ?Cart
    {
        $eloquentCart = EloquentCart::where('id', $id)
            ->whereNull('order_id')
            ->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    /**
     * Получение корзины со всеми зависимостями
     * @param int $id
     * @return Cart|null
     */
    public function unorderedWithProductsAndPromocode(int $id): ?Cart
    {
        $eloquentCart = EloquentCart::with(['cartProducts', 'promocode.discount'])
            ->where('id', $id)
            ->whereNull('order_id')
            ->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    public function unorderedWithProductsAndPromocodeByUserId(int $userId): ?Cart
    {
        $eloquentCart = EloquentCart::with(['cartProducts', 'promocode.discount'])
            ->where('user_id', $userId)
            ->whereNull('order_id')
            ->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    public function createEmpty(Cart $cart): Cart
    {
        $eloquentCart = EloquentCart::firstOrCreate([
            'user_id' => $cart->getUserId(),
        ]);

        return $this->eloquentToDomain($eloquentCart);
    }

    /**
     * Сохранение корзины и всех зависимостей
     * TODO в идеале не удалять все зависимости, а потом заново сохранять, а затрагивать только измененные данные
     * @param Cart $cart
     * @return void
     */
    public function save(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            /** @var EloquentCart $eloquentCart */
            $eloquentCart = EloquentCart::where('id', $cart->getId())->first();

            $eloquentCart->promocode()->disassociate();
            if ($cart->hasPromocode()) {
                $eloquentCart->promocode()->associate($cart->getPromocode()->getId());
            }
            $eloquentCart->save();

            $eloquentCart->cartProducts()->delete();
            $eloquentCart->cartProducts()->createMany(array_map(fn(CartProduct $cartProduct) => [
                'product_id' => $cartProduct->getProductId(),
                'quantity' => $cartProduct->getQuantity(),
                'base_price' => $cartProduct->getBasePrice(),
                'final_price' => $cartProduct->getFinalPrice(),
            ], $cart->getCartProducts()));
        });
    }

    private function eloquentToDomain(EloquentCart $eloquentCart): Cart
    {
        return CartMapper::fromEloquent($eloquentCart);
    }
}
