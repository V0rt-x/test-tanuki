<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent;

use App\Application\Cart\Mappers\CartMapper;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartProduct;
use App\Domain\Cart\Repositories\CartRepositoryInterface;
use App\Domain\Discount\Exceptions\ExcessiveDiscountValueException;
use App\Domain\Discount\Models\Promocode;
use App\Infrastructure\Eloquent\Models\Cart as EloquentCart;
use Illuminate\Support\Facades\DB;

class EloquentCartRepository implements CartRepositoryInterface
{
    /**
     * @inheritDoc
     * @throws ExcessiveDiscountValueException
     */
    public function getUnordered(int $id, array $with = []): ?Cart
    {
        $eloquentCart = EloquentCart::with($with)
            ->where('id', $id)
            ->whereNull('order_id')
            ->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    /**
     * @inheritDoc
     * @throws ExcessiveDiscountValueException
     */
    public function getUnorderedByUserId(int $userId, array $with = []): ?Cart
    {
        $eloquentCart = EloquentCart::with(['cartProducts', 'promocode.discount'])
            ->where('user_id', $userId)
            ->whereNull('order_id')
            ->first();

        return $eloquentCart ? $this->eloquentToDomain($eloquentCart) : null;
    }

    /**
     * @inheritDoc
     * @throws ExcessiveDiscountValueException
     */
    public function createEmpty(Cart $cart): Cart
    {
        $eloquentCart = EloquentCart::firstOrCreate([
            'user_id' => $cart->getUserId(),
        ]);

        return $this->eloquentToDomain($eloquentCart);
    }

    /**
     * @inheritDoc
     * TODO в идеале не удалять все зависимости, а потом заново сохранять, а затрагивать только измененные данные
     */
    public function save(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            /** @var EloquentCart $eloquentCart */
            $eloquentCart = EloquentCart::where('id', $cart->getId())->first();

            $eloquentCart->promocode()->disassociate();
            if ($cart->hasPromocode()) {
                $eloquentCart->promocode()->associate($cart->getPromocode()->getId());
            }

            $eloquentCart->discount()->disassociate();
            if ($cart->hasDiscount()) {
                $eloquentCart->discount()->associate($cart->getDiscount()->getId());
            }

            $eloquentCart->save();

            $eloquentCart->cartProducts()->delete();
            $eloquentCart->cartProducts()->createMany(array_map(fn(CartProduct $cartProduct) => [
                'product_id' => $cartProduct->getProductId(),
                'quantity' => $cartProduct->getQuantity(),
                'base_price' => $cartProduct->getBasePrice(),
                'final_price' => $cartProduct->getFinalPrice(),
            ], $cart->getCartProducts()));
        });
    }

    /**
     * @param EloquentCart $eloquentCart
     * @return Cart
     * @throws ExcessiveDiscountValueException
     */
    private function eloquentToDomain(EloquentCart $eloquentCart): Cart
    {
        return CartMapper::fromEloquent($eloquentCart);
    }
}
