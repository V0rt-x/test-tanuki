<?php
declare(strict_types=1);

namespace Domain\Discount\Models;


use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;
use App\Domain\Discount\Models\Discount;
use App\Domain\Discount\Models\Promocode;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Traits\CreatesCart;

class DiscountTest extends TestCase
{
    use CreatesCart;
//    new Promocode("TEST", 1, 5, new Discount(10000, DiscountType::PERCENT, 12, 1))
    public function test_excessive_discount_value_error()
    {
        $this->expectException(ExcessiveDiscountValueException::class);

        new Discount(0, DiscountType::PERCENT, 101);
    }

    public function test_apply_discount_to_cart()
    {
        $cart = $this->makeUnorderedCart();

        $discount = new Discount(0, DiscountType::PERCENT, 12, 1);

        $discount->applyToCart($cart);

        $this->assertTrue(
            abs((int)round($cart->getProduct(1)->getBasePrice() * 0.88) - $cart->getProduct(1)->getFinalPrice()) <= 1
        );
        $this->assertTrue(
            abs((int)round($cart->getProduct(2)->getBasePrice() * 0.88) - $cart->getProduct(2)->getFinalPrice()) <= 1
        );
        $this->assertTrue(
            abs((int)round($cart->getProduct(6)->getBasePrice() * 0.88) - $cart->getProduct(6)->getFinalPrice()) <= 1
        );
    }

    public function test_discount_inapplicable_error()
    {
        $this->expectException(DiscountInapplicableException::class);

        $cart = $this->makeUnorderedCart();


        $threshold = 100000000;

        $this->assertGreaterThan($cart->getTotalBaseSum(), $threshold);

        $discount = new Discount($threshold, DiscountType::PERCENT, 12, 1);

        $discount->applyToCart($cart);
    }

    public function test_apply_promocode_to_cart()
    {
        $cart = $this->makeUnorderedCart();

        $promocode = new Promocode("TEST", 1, 5, new Discount(10000, DiscountType::PERCENT, 15, 1));

        $promocode->applyToCart($cart);

        $this->assertTrue(
            abs((int)round($cart->getProduct(1)->getBasePrice() * 0.85) - $cart->getProduct(1)->getFinalPrice()) <= 1
        );
        $this->assertTrue(
            abs((int)round($cart->getProduct(2)->getBasePrice() * 0.85) - $cart->getProduct(2)->getFinalPrice()) <= 1
        );
        $this->assertTrue(
            abs((int)round($cart->getProduct(6)->getBasePrice() * 0.85) - $cart->getProduct(6)->getFinalPrice()) <= 1
        );
    }
}
