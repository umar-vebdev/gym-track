<?php

namespace App\Modules\Memberships\Services;

use App\Modules\Memberships\Models\MembershipType;
use App\Modules\Memberships\Repositories\MembershipTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис типов абонементов.
 */
class MembershipTypeService
{
    /**
     * @param MembershipTypeRepositoryInterface $repository
     */
    public function __construct(
        private readonly MembershipTypeRepositoryInterface $repository
    ) {}

    /**
     * Список типов абонементов.
     *
     * @param bool $onlyActive
     *
     * @return Collection<int, MembershipType>
     */
    public function list(bool $onlyActive = true): Collection
    {
        return $this->repository->all($onlyActive);
    }

    /**
     * Найти по ID.
     *
     * @param int $id
     *
     * @return MembershipType|null
     */
    public function findById(int $id): ?MembershipType
    {
        return $this->repository->findById($id);
    }

    /**
     * Создать новый тип.
     *
     * @param array<string, mixed> $data
     *
     * @return MembershipType
     */
    public function create(array $data): MembershipType
    {
        return $this->repository->create($data);
    }

    /**
     * Обновить тип.
     *
     * @param MembershipType       $type
     * @param array<string, mixed> $data
     *
     * @return MembershipType
     */
    public function update(MembershipType $type, array $data): MembershipType
    {
        return $this->repository->update($type, $data);
    }

    /**
     * Переключить активность.
     *
     * @param MembershipType $type
     *
     * @return MembershipType
     */
    public function toggleActive(MembershipType $type): MembershipType
    {
        return $this->repository->toggleActive($type);
    }
}
