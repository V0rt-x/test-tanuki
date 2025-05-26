<?php
declare(strict_types=1);

namespace Application\Cart\Handlers;

use App\Application\Cart\Commands\CartPromocodeApplyCommand;
use App\Application\Cart\Handlers\CartPromocodeApplyHandler;
use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Exceptions\PromocodeAlreadyUsedException;
use App\Domain\Cart\Exceptions\PromocodeNotFoundException;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Models\Discount;
use App\Domain\Discount\Models\Promocode;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;
use App\Domain\Discount\Repositories\PromocodeRepositoryInterface;
use App\Domain\Product\Gateways\ProductGatewayInterface;
use App\Infrastructure\Mock\MockProductGateway;
use Illuminate\Contracts\Container\BindingResolutionException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Traits\CreatesCart;

class CartPromocodeApplyHandlerTest extends TestCase
{
    use CreatesCart;

    public function test_apply_promocode_successfully()
    {
        $handler = $this->makeMockedHandler();
        $command = new CartPromocodeApplyCommand(3, "TEST");

        $this->assertNull($handler->handle($command));
    }

    public function test_promocode_not_found_error()
    {
        $this->expectException(PromocodeNotFoundException::class);

        $promocodeRepository = $this->createMock(PromocodeRepositoryInterface::class);
        $promocodeRepository
            ->method('getByCodeWithDiscount')
            ->willReturn(null);

        $handler = $this->makeMockedHandler(promocodeRepository: $promocodeRepository);
        $command = new CartPromocodeApplyCommand(3, "TEST");

        $handler->handle($command);
    }

    public function test_promocode_already_used_error()
    {
        $this->expectException(PromocodeAlreadyUsedException::class);

        $cartRepository = $this->createMock(CartRepositoryInterface::class);
        $cartRepository
            ->method('getUnordered')
            ->willReturn($this->makeCartWithPromocode());
        $cartRepository
            ->method('save');

        $handler = $this->makeMockedHandler($cartRepository);
        $command = new CartPromocodeApplyCommand(3, "TEST");

        $handler->handle($command);
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    private function makeMockedHandler(
        CartRepositoryInterface      $cartRepository = null,
        PromocodeRepositoryInterface $promocodeRepository = null,
        DiscountRepositoryInterface  $discountRepository = null,
    ): CartPromocodeApplyHandler
    {
        if (null === $cartRepository) {
            $cartRepository = $this->createMock(CartRepositoryInterface::class);
            $cartRepository
                ->method('getUnordered')
                ->willReturn($this->makeUnorderedCart());
            $cartRepository
                ->method('save');
        }

        if (null === $promocodeRepository) {
            $promocodeRepository = $this->createMock(PromocodeRepositoryInterface::class);
            $promocodeRepository
                ->method('getByCodeWithDiscount')
                ->willReturn(new Promocode('TEST', 1, 1, new Discount(10000, DiscountType::PERCENT, 10, 1)));
        }

        if (null === $discountRepository) {
            $discountRepository = $this->createMock(DiscountRepositoryInterface::class);
            $discountRepository
                ->method('getGreatestApplicableWithoutPromocodes')
                ->willReturn(null);
        }

        app()->bind(ProductGatewayInterface::class, MockProductGateway::class);
        app()->instance(CartRepositoryInterface::class, $cartRepository);
        app()->instance(PromocodeRepositoryInterface::class, $promocodeRepository);
        app()->instance(DiscountRepositoryInterface::class, $discountRepository);

        return app()->make(CartPromocodeApplyHandler::class);
    }
}
