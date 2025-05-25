<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Commands\CartProductRemoveCommand;
use App\Application\Commands\CartProductStoreCommand;
use App\Application\Handlers\CartProductRemoveHandler;
use App\Application\Handlers\CartProductStoreHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\CartProductsCapacityExceededException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\ProductNotFoundException;
use App\Http\Requests\CartProductRemoveRequest;
use App\Http\Requests\CartProductStoreRequest;
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

    public function update()
    {
        // TODO
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
