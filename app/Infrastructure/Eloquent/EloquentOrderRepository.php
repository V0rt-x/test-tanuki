<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

use App\Application\Cart\Mappers\CartMapper;
use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Eloquent\Models\Order as EloquentOrder;
use App\Infrastructure\Eloquent\Models\Cart as EloquentCart;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(Order $order)
    {
        DB::transaction(function () use ($order) {
            /** @var EloquentOrder $eloquentOrder */
            $eloquentOrder = EloquentOrder::create([
                'phone' => $order->getPhone(),
            ]);

            EloquentCart::where('id', $order->getCartId())
                ->update([
                    'order_id' => $eloquentOrder->id
                ]);
        });
    }

    public function getWithCart(int $orderId): ?Order
    {
        $eloquentOrder = EloquentOrder::with('cart')->where('id', $orderId)->first();

        return $eloquentOrder ? $this->eloquentToDomain($eloquentOrder) : null;
    }

    public function getWithCartAndDependencies(int $orderId): ?Order
    {
        $eloquentOrder = EloquentOrder::with(['cart.cartProducts', 'cart.promocode.discount'])->where('id', $orderId)->first();

        return $eloquentOrder ? $this->eloquentToDomain($eloquentOrder) : null;
    }

    private function eloquentToDomain(EloquentOrder $eloquentOrder): Order
    {
        if ($eloquentOrder->relationLoaded('cart')) {
            $cart = CartMapper::fromEloquent($eloquentOrder->cart);
            return new Order(
                $eloquentOrder->phone,
                $eloquentOrder->id,
                $cart->getId(),
                $cart,
            );
        }

        return new Order(
            $eloquentOrder->phone,
            $eloquentOrder->id,
        );
    }
}
