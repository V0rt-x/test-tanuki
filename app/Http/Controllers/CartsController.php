<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Commands\CartCreateCommand;
use App\Application\Commands\CartGetCommand;
use App\Application\Handlers\CartCreateHandler;
use App\Application\Handlers\CartGetHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Models\CartProduct;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartGetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CartsController extends Controller
{
    /**
     * @param CartGetRequest $request
     * @param CartGetHandler $handler
     * @return JsonResponse
     * @throws CartNotFoundException
     * @throws DependencyNotLoadedException
     */
    public function get(
        CartGetRequest $request,
        CartGetHandler $handler
    ): JsonResponse
    {
        $command = new CartGetCommand(
            $request->validated('cart_id')
        );

        $cart = $handler->handle($command);

        return response()->json([
            'id' => $cart->getId(),
            'cart_products' => array_map(fn(CartProduct $cartProduct) => [
                'product_id' => $cartProduct->getProductId(),
                'base_price' => $cartProduct->getBasePrice(),
                'final_price' => $cartProduct->getFinalPrice(),
            ], $cart->getCartProducts()),
            'promocode' => $cart->getPromocode() ? [
                'id' => $cart->getPromocode()->getId(),
                'code' => $cart->getPromocode()->getCode(),
            ] : null,
            'discount' => [], // TODO
            'total_base_sum' => $cart->totalBaseSum(),
            'total_final_sum' => $cart->totalFinalSum(),
        ]);
    }

    public function create(
        CartCreateRequest $request,
        CartCreateHandler $handler
    ): Response
    {
        $command = new CartCreateCommand();

        $handler->handle($command);

        return response()->noContent();
    }
}
