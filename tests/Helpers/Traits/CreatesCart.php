<?php
declare(strict_types=1);

namespace Tests\Helpers\Traits;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Discount\Models\Discount;
use App\Domain\Discount\Models\Promocode;

trait CreatesCart
{
    public function makeEmptyCart(array $params = []): Cart
    {
        return new Cart(
            $params['user_id'] ?? 555,
            $params['id'] ?? 2,
            $params['order_id'] ?? null,
            [],
        );
    }

    public function makeUnorderedCart(array $params = []): Cart
    {
        return new Cart(
            $params['user_id'] ?? 555,
            $params['id'] ?? 3,
            $params['order_id'] ?? null,
            $params['cart_products'] ?? [
            new CartProduct(1, 4, 87312, $params['id'] ?? 3, 8732, 1),
            new CartProduct(2, 7, 53622, $params['id'] ?? 3, 4632, 1),
            new CartProduct(6, 10, 23489, $params['id'] ?? 3, 9180, 1),
        ],
            $params['discount_id'] ?? null,
            $params['promocode_id'] ?? null,
            $params['discount'] ?? null,
            $params['promocode'] ?? null,
        );
    }

    public function makeVeryBigCart(array $params = []): Cart
    {
        $cartProducts = [];
        for ($i = 0; $i < Cart::MAX_PRODUCT_CAPACITY - 1; $i++) {
            $cartProducts[] = new CartProduct($i + 2, 1, 10000, $params['id'] ?? 3, null, $i + 5);
        }

        return new Cart(
            $params['user_id'] ?? 555,
            $params['id'] ?? 3,
            $params['order_id'] ?? null,
            $cartProducts,
            $params['discount_id'] ?? null,
            $params['promocode_id'] ?? null,
            $params['discount'] ?? null,
            $params['promocode'] ?? null,
        );
    }

    public function makeCartWithPromocode(array $params = []): Cart
    {
        return new Cart(
            $params['user_id'] ?? 555,
            $params['id'] ?? 3,
            $params['order_id'] ?? null,
            $params['cart_products'] ?? [
            new CartProduct(1, 4, 87312, $params['id'] ?? 3, null, 1),
            new CartProduct(2, 7, 53622, $params['id'] ?? 3, null, 1),
            new CartProduct(6, 10, 23489, $params['id'] ?? 3, null, 1),
        ],
            $params['discount_id'] ?? null,
            1,
            $params['discount'] ?? null,
            new Promocode('TEST', 1, 1, new Discount(10000, DiscountType::PERCENT, 10, 1)),
        );
    }

    public function makeLowPriceCart(array $params = []): Cart
    {
        return new Cart(
            $params['user_id'] ?? 555,
            $params['id'] ?? 3,
            $params['order_id'] ?? null,
            $params['cart_products'] ?? [
            new CartProduct(1, 1, 10000, $params['id'] ?? 3, null, 1),
            new CartProduct(2, 1, 10000, $params['id'] ?? 3, null, 2),
        ],
            $params['discount_id'] ?? null,
            $params['promocode_id'] ?? null,
            $params['discount'] ?? null,
            $params['promocode'] ?? null,
        );
    }

    public function makeOrderedCart(array $params = [])
    {
        return new Cart(
            $params['user_id'] ?? 555,
            $params['id'] ?? 677,
            $params['order_id'] ?? 83721,
            $params['cart_products'] ?? [
                new CartProduct(1, 4, 87312, $params['id'] ?? 3, null, 1),
                new CartProduct(2, 7, 53622, $params['id'] ?? 3, null, 1),
                new CartProduct(6, 10, 23489, $params['id'] ?? 3, null, 1),
            ],
            $params['discount_id'] ?? null,
            $params['promocode_id'] ?? null,
            $params['discount'] ?? null,
            $params['promocode'] ?? null,
        );
    }
}
