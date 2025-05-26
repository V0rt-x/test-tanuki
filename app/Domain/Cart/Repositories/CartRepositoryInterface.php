<?php
declare(strict_types=1);

namespace App\Domain\Cart\Repositories;

use App\Domain\Cart\Models\Cart;

interface CartRepositoryInterface
{
    /**
     * Создание пустой корзины
     * @param Cart $cart
     * @return Cart
     */
    public function createEmpty(Cart $cart): Cart;

    /**
     * Сохранение корзины и всех зависимостей
     * @param Cart $cart
     * @return void
     */
    public function save(Cart $cart): void;

    /**
     * Получение не привязанной к заказу корзины с указанными зависимостями
     * @param int $id
     * @param array $with массив зависимостей. Если массив пустой, зависимости не подгружаются.
     * @return Cart|null
     */
    public function getUnordered(int $id, array $with = []): ?Cart;

    /**
     * Получение не привязанной к заказу корзины по id пользователя с указанными зависимостями
     * @param int $userId
     * @param array $with массив зависимостей. Если массив пустой, зависимости не подгружаются.
     * @return Cart|null
     */
    public function getUnorderedByUserId(int $userId, array $with = []): ?Cart;
}
