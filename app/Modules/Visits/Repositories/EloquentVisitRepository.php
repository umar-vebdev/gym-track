<?php

namespace App\Modules\Visits\Repositories;

use App\Modules\Visits\Models\Visit;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-реализация репозитория посещений.
 */
class EloquentVisitRepository implements VisitRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function paginate(?int $clientId = null, ?string $date = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Visit::with(['client', 'membershipPurchase.membershipType']);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($date) {
            $query->whereDate('visited_at', $date);
        }

        return $query->orderByDesc('visited_at')->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Visit
    {
        return Visit::with(['client', 'membershipPurchase.membershipType'])->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Visit
    {
        $visit = Visit::create($data);

        return $visit->load(['client', 'membershipPurchase.membershipType']);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Visit $visit): bool
    {
        return $visit->delete();
    }
}
