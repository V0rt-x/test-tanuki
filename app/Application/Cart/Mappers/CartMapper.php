<?php
declare(strict_types=1);

namespace App\Application\Cart\Mappers;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Discount\Models\Promocode;
use App\Infrastructure\Eloquent\Models\Cart as EloquentCart;

class CartMapper
{
    public static function fromEloquent(EloquentCart $eloquentCart): Cart
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
            $eloquentCart->user_id,
            $eloquentCart->id,
            $eloquentCart->order_id,
            $cartProducts ?? [],
            $eloquentCart->promocode_id,
            $promocode ?? null,
        );
    }
}
