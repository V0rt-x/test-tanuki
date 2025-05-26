<?php
declare(strict_types=1);

namespace Domain\Cart\Models;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Discount\Models\Discount;
use App\Domain\Discount\Models\Promocode;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Traits\CreatesCart;

class CartTest extends TestCase
{
    use CreatesCart;

    public function test_has_product()
    {
        $cart = $this->makeUnorderedCart();

        $this->assertTrue($cart->hasProduct(1));
    }

    public function test_add_product()
    {
        $cart = $this->makeEmptyCart();

        $this->assertTrue(!$cart->hasProduct(1));

        $cart->addProduct(new CartProduct(1, 4, 87312, 3, null, 1),);

        $this->assertTrue($cart->hasProduct(1));
        $this->assertEquals(4, $cart->getProduct(1)->getQuantity());
        $this->assertEquals(87312, $cart->getProduct(1)->getBasePrice());
    }

    public function test_remove_product()
    {
        $cart = $this->makeUnorderedCart();

        $this->assertTrue($cart->hasProduct(1));

        $cart->removeProduct(1);

        $this->assertTrue(!$cart->hasProduct(1));
        $this->assertTrue($cart->hasProduct(2));
        $this->assertTrue($cart->hasProduct(6));
    }

    public function test_update_product()
    {
        $cart = $this->makeUnorderedCart();

        $this->assertTrue($cart->hasProduct(1));
        $this->assertEquals(4, $cart->getProduct(1)->getQuantity());

        $cart->updateProduct(new CartProduct(1, 6, 6666,  5, null, 1));

        $this->assertTrue($cart->hasProduct(1));
        $this->assertEquals(6, $cart->getProduct(1)->getQuantity());
        $this->assertEquals(6666, $cart->getProduct(1)->getBasePrice());
        $this->assertEquals(3, $cart->getProduct(1)->getCartId());
    }

    public function test_get_total_base_sum()
    {
        $cart = $this->makeUnorderedCart();

        $this->assertEquals(959492, $cart->getTotalBaseSum());
    }

    public function test_get_total_final_sum()
    {
        $cart = $this->makeUnorderedCart();

        $this->assertEquals(159152, $cart->getTotalFinalSum());
    }

    public function test_get_total_quantity()
    {
        $cart = $this->makeUnorderedCart();

        $this->assertEquals(21, $cart->getTotalQuantity());
    }
}
