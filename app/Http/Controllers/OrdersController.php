<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Order\Commands\OrderCreateCommand;
use App\Application\Order\Commands\OrderGetCommand;
use App\Application\Order\Handlers\OrderCreateHandler;
use App\Application\Order\Handlers\OrderGetHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Order\Exceptions\CartCannotBeOrderedException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderGetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrdersController extends Controller
{
    /**
     * @param OrderCreateRequest $request
     * @param OrderCreateHandler $handler
     * @return Response
     * @throws CartCannotBeOrderedException
     * @throws CartNotFoundException
     */
    public function create(
        OrderCreateRequest $request,
        OrderCreateHandler $handler,
    ): Response
    {
        $command = new OrderCreateCommand(
            (int)$request->validated('user_id'),
            $request->validated('phone'),
        );

        $handler->handle($command);

        return response()->noContent();
    }

    /**
     * @param OrderGetRequest $request
     * @param OrderGetHandler $handler
     * @return JsonResponse
     * @throws OrderNotFoundException
     */
    public function get(
        OrderGetRequest $request,
        OrderGetHandler $handler,
    ): JsonResponse
    {
        $command = new OrderGetCommand(
            $request->validated('order_id'),
        );

        $order = $handler->handle($command);

        return response()->json([
            ''
        ]);
    }
}
