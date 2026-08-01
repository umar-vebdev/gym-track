<?php

namespace App\Modules\Visits\Repositories;

use App\Modules\Visits\Models\Visit;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория посещений.
 */
interface VisitRepositoryInterface
{
    /**
     * Список визитов с пагинацией и фильтрами.
     *
     * @param int|null    $clientId ID клиента
     * @param string|null $date     Дата в формате YYYY-MM-DD
     * @param int         $perPage  Кол-во на страницу
     *
     * @return LengthAwarePaginator
     */
    public function paginate(?int $clientId = null, ?string $date = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Найти визит по ID.
     *
     * @param int $id
     *
     * @return Visit|null
     */
    public function findById(int $id): ?Visit;

    /**
     * Зафиксировать новый визит.
     *
     * @param array<string, mixed> $data
     *
     * @return Visit
     */
    public function create(array $data): Visit;

    /**
     * Отменить/удалить запись о визите.
     *
     * @param Visit $visit
     *
     * @return bool
     */
    public function delete(Visit $visit): bool;
}
