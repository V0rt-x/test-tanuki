<?php
declare(strict_types=1);

namespace Application\Order\Handlers;

use App\Application\Cart\Handlers\CartProductStoreHandler;
use App\Application\Order\Commands\OrderCreateCommand;
use App\Application\Order\Handlers\OrderCreateHandler;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;
use App\Domain\Order\Exceptions\CartCannotBeOrderedException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Gateways\ProductGatewayInterface;
use App\Domain\Shared\Models\ValueObjects\Phone;
use App\Infrastructure\Mock\MockProductGateway;
use Illuminate\Contracts\Container\BindingResolutionException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Traits\CreatesCart;

class OrderCreateHandlerTest extends TestCase
{
    use CreatesCart;

    public function test_order_create_successfully()
    {
        $command = new OrderCreateCommand(555, new Phone('79999999999'));
        $handler = $this->makeMockedHandler();

        $this->assertNull($handler->handle($command));
    }

    public function test_cart_cannot_be_ordered_error()
    {
        $this->expectException(CartCannotBeOrderedException::class);

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnorderedByUserId')
            ->willReturn($this->makeLowPriceCart());

        $command = new OrderCreateCommand(555, new Phone('79999999999'));
        $handler = $this->makeMockedHandler($cartRepository);

        $this->assertNull($handler->handle($command));
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    private function makeMockedHandler(
        CartRepositoryInterface $cartRepository = null,
        OrderRepositoryInterface $orderRepository = null,
        DiscountRepositoryInterface $discountRepository = null,
    ): OrderCreateHandler
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
                ->method('create');
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

        return app()->make(OrderCreateHandler::class);
    }
}
