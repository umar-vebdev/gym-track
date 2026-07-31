<?php

namespace App\Modules\Memberships\Repositories;

use App\Modules\Memberships\Models\MembershipType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Контракт репозитория типов абонементов.
 */
interface MembershipTypeRepositoryInterface
{
    /**
     * Получить список всех или только активных типов абонементов.
     *
     * @param bool $onlyActive Фильтр по активным
     *
     * @return Collection<int, MembershipType>
     */
    public function all(bool $onlyActive = true): Collection;

    /**
     * Найти тип абонемента по ID.
     *
     * @param int $id
     *
     * @return MembershipType|null
     */
    public function findById(int $id): ?MembershipType;

    /**
     * Создать новый тип абонемента.
     *
     * @param array<string, mixed> $data
     *
     * @return MembershipType
     */
    public function create(array $data): MembershipType;

    /**
     * Обновить тип абонемента.
     *
     * @param MembershipType       $type
     * @param array<string, mixed> $data
     *
     * @return MembershipType
     */
    public function update(MembershipType $type, array $data): MembershipType;

    /**
     * Переключить статус активности.
     *
     * @param MembershipType $type
     *
     * @return MembershipType
     */
    public function toggleActive(MembershipType $type): MembershipType;
}
