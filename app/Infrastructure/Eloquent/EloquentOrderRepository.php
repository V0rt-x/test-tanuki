<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

use App\Application\Cart\Mappers\CartMapper;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Shared\Exceptions\InvalidPhoneFormatException;
use App\Domain\Shared\Models\ValueObjects\Phone;
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
                'phone' => $order->getPhone()->value,
            ]);

            EloquentCart::where('id', $order->getCartId())
                ->update([
                    'order_id' => $eloquentOrder->id
                ]);
        });
    }

    /**
     * @throws InvalidPhoneFormatException
     * @throws ExcessiveDiscountValueException
     */
    public function get(int $orderId, array $with = []): ?Order
    {
        $eloquentOrder = EloquentOrder::with($with)->where('id', $orderId)->first();

        return $eloquentOrder ? $this->eloquentToDomain($eloquentOrder) : null;
    }

    /**
     * @throws InvalidPhoneFormatException
     * @throws ExcessiveDiscountValueException
     */
    private function eloquentToDomain(EloquentOrder $eloquentOrder): Order
    {
        if ($eloquentOrder->relationLoaded('cart')) {
            $cart = CartMapper::fromEloquent($eloquentOrder->cart);
            return new Order(
                new Phone($eloquentOrder->phone),
                $eloquentOrder->id,
                $cart->getId(),
                $cart,
            );
        }

        return new Order(
            new Phone($eloquentOrder->phone),
            $eloquentOrder->id,
        );
    }
}
