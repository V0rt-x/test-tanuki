<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Commands\CartPromocodeApplyCommand;
use App\Application\Commands\CartPromocodeRemoveCommand;
use App\Application\Handlers\CartPromocodeApplyHandler;
use App\Application\Handlers\CartPromocodeRemoveHandler;
use App\Domain\Cart\Exceptions\CartNotFoundException;
use App\Domain\Cart\Exceptions\DependencyNotLoadedException;
use App\Domain\Cart\Exceptions\DiscountInapplicableException;
use App\Domain\Cart\Exceptions\PromocodeAlreadyUsedException;
use App\Domain\Cart\Exceptions\PromocodeNotFoundException;
use App\Http\Requests\CartPromocodeApplyRequest;
use App\Http\Requests\CartPromocodeRemoveRequest;
use Illuminate\Http\Response;

class CartPromocodesController extends Controller
{
    /**
     * @param CartPromocodeApplyRequest $request
     * @param CartPromocodeApplyHandler $handler
     * @return Response
     * @throws CartNotFoundException
     * @throws PromocodeNotFoundException
     * @throws DiscountInapplicableException
     * @throws PromocodeAlreadyUsedException
     * @throws DependencyNotLoadedException
     */
    public function apply(
        CartPromocodeApplyRequest $request,
        CartPromocodeApplyHandler $handler
    ): Response
    {
        $command = new CartPromocodeApplyCommand(
            $request->validated('cart_id'),
            $request->validated('promocode'),
        );

        $handler->handle($command);

        return response()->noContent();
    }

    /**
     * @param CartPromocodeRemoveRequest $request
     * @param CartPromocodeRemoveHandler $handler
     * @return Response
     * @throws CartNotFoundException
     * @throws PromocodeNotFoundException
     * @throws DiscountInapplicableException
     * @throws DependencyNotLoadedException
     */
    public function remove(
        CartPromocodeRemoveRequest $request,
        CartPromocodeRemoveHandler $handler
    ): Response
    {
        $command = new CartPromocodeRemoveCommand(
            $request->validated('cart_id'),
            $request->validated('promocode'),
        );

        $handler->handle($command);

        return response()->noContent();
    }
}
