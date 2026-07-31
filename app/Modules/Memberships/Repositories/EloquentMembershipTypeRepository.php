<?php

namespace App\Modules\Memberships\Repositories;

use App\Modules\Memberships\Models\MembershipType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent-реализация репозитория типов абонементов.
 */
class EloquentMembershipTypeRepository implements MembershipTypeRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function all(bool $onlyActive = true): Collection
    {
        $query = MembershipType::query();

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?MembershipType
    {
        return MembershipType::find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): MembershipType
    {
        return MembershipType::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(MembershipType $type, array $data): MembershipType
    {
        $type->update($data);

        return $type->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function toggleActive(MembershipType $type): MembershipType
    {
        $type->update(['is_active' => !$type->is_active]);

        return $type->refresh();
    }
}
