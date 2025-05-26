<?php
declare(strict_types=1);

namespace Application\Order\Handlers;

use App\Application\Order\Commands\OrderGetCommand;
use App\Application\Order\Handlers\OrderCreateHandler;
use App\Application\Order\Handlers\OrderGetHandler;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Gateways\ProductGatewayInterface;
use App\Domain\Shared\Models\ValueObjects\Phone;
use App\Infrastructure\Mock\MockProductGateway;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Traits\CreatesCart;

class OrderGetHandlerTest extends TestCase
{
    use CreatesCart;

    public function test_order_get_successfully(): void
    {
        $command = new OrderGetCommand(83721);

        $handler = $this->makeMockedHandler();

        $order = $handler->handle($command);

        $this->assertEquals(83721, $order->getId());
        $this->assertEquals(677, $order->getCartId());
        $this->assertEquals(677, $order->getCart()->getId());
        $this->assertEquals(83721, $order->getCart()->getOrderId());
        $this->assertEquals('79999999999', $order->getPhone()->value);
        $this->assertTrue($order->getCart()->hasProduct(1));
        $this->assertTrue(!$order->getCart()->hasProduct(4));
    }

    public function test_order_not_found_error(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $orderRepository
            ->method('get')
            ->willReturn(null);

        $command = new OrderGetCommand(83721);

        $handler = $this->makeMockedHandler(orderRepository: $orderRepository);

        $handler->handle($command);
    }

    private function makeMockedHandler(
        CartRepositoryInterface $cartRepository = null,
        OrderRepositoryInterface $orderRepository = null,
        DiscountRepositoryInterface $discountRepository = null,
    ): OrderGetHandler
    {
        if (null === $cartRepository) {
            $cartRepository = $this->createMock(CartRepositoryInterface::class);
            $cartRepository
                ->method('getUnorderedByUserId')
                ->willReturn($this->makeUnorderedCart());
            $cartRepository
                ->method('save');
        }

        if (null === $orderRepository) {
            $orderRepository = $this->createMock(OrderRepositoryInterface::class);
            $orderRepository
                ->method('get')
                ->willReturn(new Order(
                    new Phone('79999999999'),
                    83721,
                    677,
                    $this->makeOrderedCart()
                ));
        }

        if (null === $discountRepository) {
            $discountRepository = $this->createMock(DiscountRepositoryInterface::class);
            $discountRepository
                ->method('getGreatestApplicableWithoutPromocodes')
                ->willReturn(null);
        }

        app()->bind(ProductGatewayInterface::class, MockProductGateway::class);
        app()->instance(CartRepositoryInterface::class, $cartRepository);
        app()->instance(OrderRepositoryInterface::class, $orderRepository);
        app()->instance(DiscountRepositoryInterface::class, $discountRepository);

        return app()->make(OrderGetHandler::class);
    }
}
