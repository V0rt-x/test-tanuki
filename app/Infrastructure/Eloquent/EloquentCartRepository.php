<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

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
    public function get(int $id): ?Cart
    {
        $eloquentCart = EloquentCart::where('id', $id)->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    /**
     * Получение корзины со всеми зависимостями
     * @param int $id
     * @return Cart|null
     */
    public function withProductsAndPromocode(int $id): ?Cart
    {
        $eloquentCart = EloquentCart::where('id', $id)->with(['cartProducts', 'promocode.discount'])->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    public function createEmpty(Cart $eloquentCart): Cart
    {
        $eloquentCart = EloquentCart::create();

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
        if ($eloquentCart->relationLoaded('cartProducts')) {
            $cartProducts = $eloquentCart->cartProducts->map(fn(object $cartProduct) => new CartProduct(
                $cartProduct->product_id,
                $cartProduct->quantity,
                $cartProduct->base_price,
                $cartProduct->cart_id,
                $cartProduct->final_price,
                $cartProduct->id,
            ))->toArray();
        }

        if ($eloquentCart->relationLoaded('promocode') && $eloquentCart->promocode !== null) {
            $promocode = new Promocode(
                $eloquentCart->promocode->code,
                $eloquentCart->promocode->discount_id,
                $eloquentCart->promocode->id,
            );
        }

        return new Cart(
            $eloquentCart->id,
            $cartProducts ?? [],
            $eloquentCart->promocode_id,
            $promocode ?? null,
        );
    }
}
