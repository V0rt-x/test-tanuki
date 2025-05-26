<?php
declare(strict_types=1);

namespace App\Providers;

use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Repositories\DiscountRepositoryInterface;
use App\Domain\Discount\Repositories\PromocodeRepositoryInterface;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Gateways\ProductGatewayInterface;
use App\Infrastructure\Eloquent\EloquentCartRepository;
use App\Infrastructure\Eloquent\EloquentDiscountRepository;
use App\Infrastructure\Eloquent\EloquentOrderRepository;
use App\Infrastructure\Eloquent\EloquentPromocodeRepository;
use App\Infrastructure\Mock\MockProductGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        CartRepositoryInterface::class => EloquentCartRepository::class,
        PromocodeRepositoryInterface::class => EloquentPromocodeRepository::class,
        ProductGatewayInterface::class => MockProductGateway::class,
        DiscountRepositoryInterface::class => EloquentDiscountRepository::class,
        OrderRepositoryInterface::class => EloquentOrderRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
