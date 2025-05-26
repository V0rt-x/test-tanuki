<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Cart\Commands\CartCreateCommand;
use App\Application\Cart\Commands\CartGetByFilterCommand;
use App\Application\Cart\Commands\CartGetCommand;
use App\Application\Cart\Handlers\CartCreateHandler;
use App\Application\Cart\Handlers\CartGetByFilterHandler;
use App\Application\Cart\Handlers\CartGetHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Models\CartProduct;
use App\Http\Requests\CartCreateRequest;
use App\Http\Requests\CartGetByFilterRequest;
use App\Http\Requests\CartGetRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CartsController extends Controller
{
    public function create(
        CartCreateRequest $request,
        CartCreateHandler $handler
    ): Response
    {
        $command = new CartCreateCommand(
            (int)$request->validated('user_id')
        );

        $handler->handle($command);

        return response()->noContent();
    }

    /**
     * Возможно, стоит свести к одному методу с getByFilter
     * @param CartGetRequest $request
     * @param CartGetHandler $handler
     * @return CartResource
     * @throws CartNotFoundException
     */
    public function get(
        CartGetRequest $request,
        CartGetHandler $handler
    ): CartResource
    {
        $command = new CartGetCommand(
            $request->validated('cart_id')
        );

        $cart = $handler->handle($command);

        return CartResource::make($cart);
    }

    /**
     * @param CartGetByFilterRequest $request
     * @param CartGetByFilterHandler $handler
     * @return CartResource
     * @throws CartNotFoundException
     */
    public function getByFilter(
        CartGetByFilterRequest $request,
        CartGetByFilterHandler $handler
    ): CartResource
    {
        $command = new CartGetByFilterCommand(
            (int)$request->validated('user_id')
        );

        $cart = $handler->handle($command);

        return CartResource::make($cart);
    }
}
