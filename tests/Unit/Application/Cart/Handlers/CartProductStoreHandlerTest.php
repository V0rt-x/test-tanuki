<?php
declare(strict_types=1);

namespace Application\Cart\Handlers;

use App\Application\Cart\Commands\CartProductStoreCommand;
use App\Application\Cart\Handlers\CartProductStoreHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\CartProductsCapacityExceededException;
use App\Domain\Cart\Exceptions\ProductAlreadyInCartException;
use App\Domain\Cart\Exceptions\ProductNotFoundException;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;
use App\Domain\Product\Gateways\ProductGatewayInterface;
use App\Infrastructure\Mock\MockProductGateway;
use Illuminate\Contracts\Container\BindingResolutionException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Traits\CreatesCart;

class CartProductStoreHandlerTest extends TestCase
{
    use CreatesCart;

    public function test_store_product_in_empty_cart_successfully(): void
    {
        $handler = $this->makeMockedHandler();
        $command = new CartProductStoreCommand(3, 5, 3);

        $this->assertNull($handler->handle($command));
    }

    public function test_store_product_in_cart_successfully(): void
    {
        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnordered')
            ->willReturn($this->makeUnorderedCart());
        $cartRepository
            ->method('save');

        $handler = $this->makeMockedHandler();
        $command = new CartProductStoreCommand(3, 5, 3);

        $this->assertNull($handler->handle($command));
    }

    public function test_product_not_found_error(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnordered')
            ->willReturn($this->makeUnorderedCart());
        $cartRepository
            ->method('save');

        $handler = $this->makeMockedHandler($cartRepository);
        $command = new CartProductStoreCommand(3, 10, 3);

        $handler->handle($command);
    }

    public function test_cart_not_found_error(): void
    {
        $this->expectException(CartNotFoundException::class);

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnordered')
            ->willReturn(null);

        $handler = $this->makeMockedHandler($cartRepository);
        $command = new CartProductStoreCommand(4, 1, 3);

        $handler->handle($command);
    }

    public function test_cart_product_capacity_exceeded_error(): void
    {
        $this->expectException(CartProductsCapacityExceededException::class);

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnordered')
            ->willReturn($this->makeVeryBigCart());

        $handler = $this->makeMockedHandler($cartRepository);
        $command = new CartProductStoreCommand(3, 1, 3);

        $handler->handle($command);
    }

    public function test_product_already_in_cart_error(): void
    {
        $this->expectException(ProductAlreadyInCartException::class);

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnordered')
            ->willReturn($this->makeUnorderedCart());

        $handler = $this->makeMockedHandler($cartRepository);
        $command = new CartProductStoreCommand(3, 6, 3);

        $handler->handle($command);
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    private function makeMockedHandler(
        CartRepositoryInterface $cartRepository = null,
        DiscountRepositoryInterface $discountRepository = null,
    ): CartProductStoreHandler
    {
        if (null === $cartRepository) {
            $cartRepository = $this->createMock(CartRepositoryInterface::class);
            $cartRepository
                ->method('getUnordered')
                ->willReturn($this->makeEmptyCart());
            $cartRepository
                ->method('save');
        }

        if (null === $discountRepository) {
            $discountRepository = $this->createMock(DiscountRepositoryInterface::class);
            $discountRepository
                ->method('getGreatestApplicableWithoutPromocodes')
                ->willReturn(null);
        }

        app()->bind(ProductGatewayInterface::class, MockProductGateway::class);
        app()->instance(CartRepositoryInterface::class, $cartRepository);
        app()->instance(DiscountRepositoryInterface::class, $discountRepository);

        return app()->make(CartProductStoreHandler::class);
    }
}
