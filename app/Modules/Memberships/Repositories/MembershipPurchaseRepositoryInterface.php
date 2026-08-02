<?php

namespace App\Modules\Memberships\Repositories;

use App\Modules\Memberships\Models\MembershipPurchase;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория проданных абонементов.
 */
interface MembershipPurchaseRepositoryInterface
{
    /**
     * Список покупок с пагинацией и фильтрами.
     *
     * @param int|null $clientId  Фильтр по клиенту
     * @param bool|null $onlyActive Фильтр по только активным
     * @param int      $perPage   Кол-во на страницу
     *
     * @return LengthAwarePaginator
     */
    public function paginate(?int $clientId = null, ?bool $onlyActive = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Найти покупку по ID.
     *
     * @param int $id
     *
     * @return MembershipPurchase|null
     */
    public function findById(int $id): ?MembershipPurchase;

    /**
     * Создать запись о покупке абонемента.
     *
     * @param array<string, mixed> $data
     *
     * @return MembershipPurchase
     */
    public function create(array $data): MembershipPurchase;

    /**
     * Списать 1 визит у абонемента по визитам.
     *
     * @param MembershipPurchase $purchase
     *
     * @return MembershipPurchase
     */
    public function decrementVisit(MembershipPurchase $purchase): MembershipPurchase;

    /**
     * Обновить запись о покупке абонемента.
     *
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return MembershipPurchase
     */
    public function update(int $id, array $data): MembershipPurchase;

    /**
     * Удалить запись о покупке абонемента.
     *
     * @param int $id
     *
     * @return void
     */
    public function delete(int $id): void;
}
