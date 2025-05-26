<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Cart\Commands\CartProductRemoveCommand;
use App\Application\Cart\Commands\CartProductStoreCommand;
use App\Application\Cart\Commands\CartProductUpdateCommand;
use App\Application\Cart\Handlers\CartProductRemoveHandler;
use App\Application\Cart\Handlers\CartProductStoreHandler;
use App\Application\Cart\Handlers\CartProductUpdateHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\CartProductsCapacityExceededException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\ProductAlreadyInCartException;
use App\Domain\Cart\Exceptions\ProductNotFoundException;
use App\Domain\Cart\Exceptions\ProductNotInCartException;
use App\Http\Requests\CartProductRemoveRequest;
use App\Http\Requests\CartProductStoreRequest;
use App\Http\Requests\CartProductUpdateRequest;
use Illuminate\Http\Response;

class CartProductsController extends Controller
{
    /**
     * @param CartProductStoreRequest $request
     * @param CartProductStoreHandler $handler
     * @return Response
     * @throws CartNotFoundException
     * @throws CartProductsCapacityExceededException
     * @throws DiscountInapplicableException
     * @throws ProductNotFoundException
     * @throws DependencyNotLoadedException
     * @throws ProductAlreadyInCartException
     */
    public function store(
        CartProductStoreRequest $request,
        CartProductStoreHandler $handler,
    ): Response
    {
        $command = new CartProductStoreCommand(
            $request->validated('cart_id'),
            $request->validated('product_id'),
            $request->validated('quantity'),
        );

        $handler->handle($command);

        return response()->noContent();
    }

    /**
     * @throws CartNotFoundException
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     * @throws ProductNotInCartException
     */
    public function update(
        CartProductUpdateRequest $request,
        CartProductUpdateHandler $handler,
    ): Response
    {
        $command = new CartProductUpdateCommand(
            $request->validated('cart_id'),
            $request->validated('product_id'),
            $request->validated('quantity'),
        );

        $handler->handle($command);

        return response()->noContent();
    }

    /**
     * @param CartProductRemoveRequest $request
     * @param CartProductRemoveHandler $handler
     * @return Response
     * @throws CartNotFoundException
     * @throws DiscountInapplicableException
     * @throws ProductNotFoundException
     * @throws DependencyNotLoadedException
     */
    public function remove(
        CartProductRemoveRequest $request,
        CartProductRemoveHandler $handler,
    ): Response
    {
        $command = new CartProductRemoveCommand(
            $request->validated('cart_id'),
            $request->validated('product_id'),
        );

        $handler->handle($command);

        return response()->noContent();
    }
}
