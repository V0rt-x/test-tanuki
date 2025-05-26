<?php
declare(strict_types=1);

namespace App\Application\Cart\Mappers;

use App\Application\Discount\Mappers\DiscountMapper;
use App\Application\Discount\Mappers\PromocodeMapper;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;
use App\Infrastructure\Eloquent\Models\Cart as EloquentCart;

class CartMapper
{
    /**
     * @throws ExcessiveDiscountValueException
     */
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
            $promocode = PromocodeMapper::fromEloquent($eloquentCart->promocode);

            return new Cart(
                $eloquentCart->user_id,
                $eloquentCart->id,
                $eloquentCart->order_id,
                $cartProducts ?? [],
                $eloquentCart->discount_id,
                $eloquentCart->promocode_id,
                null,
                $promocode,
            );
        }

        if ($eloquentCart->relationLoaded('discount') && $eloquentCart->discount !== null) {
            $discount = DiscountMapper::fromEloquent($eloquentCart->discount);
        }

        return new Cart(
            $eloquentCart->user_id,
            $eloquentCart->id,
            $eloquentCart->order_id,
            $cartProducts ?? [],
            $eloquentCart->discount_id,
            $eloquentCart->promocode_id,
            $discount ?? null,
        );
    }
}
