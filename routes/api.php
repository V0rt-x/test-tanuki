<?php
declare(strict_types=1);

use App\Http\Controllers\CartProductsController;
use App\Http\Controllers\CartPromocodesController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\OrdersController;
use Illuminate\Support\Facades\Route;

Route::prefix('/carts')->group(function () {
    Route::post('/', [CartsController::class, 'create']);
    Route::get('/{cart_id}', [CartsController::class, 'get']);
    Route::get('/', [CartsController::class, 'getByFilter']);

    Route::prefix('/{cart_id}')->group(function () {
        Route::prefix('/products')->group(function () {
            Route::post('/', [CartProductsController::class, 'store']);
            Route::patch('/{product_id}', [CartProductsController::class, 'update']);
            Route::delete('/{product_id}', [CartProductsController::class, 'remove']);
        });

        Route::prefix('/promocode')->group(function () {
            Route::post('/', [CartPromocodesController::class, 'apply']);
            Route::delete('/{promocode}', [CartPromocodesController::class, 'remove']);
        });
    });
});

Route::prefix('/orders')->group(function () { // TODO
    Route::post('/', [OrdersController::class, 'create']);
    Route::get('/{order_id}', [OrdersController::class, 'get']);
});
