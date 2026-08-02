<?php

namespace App\Modules\Memberships\Repositories;

use App\Modules\Memberships\Models\MembershipPurchase;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-реализация репозитория продаж абонементов.
 */
class EloquentMembershipPurchaseRepository implements MembershipPurchaseRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function paginate(?int $clientId = null, ?bool $onlyActive = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = MembershipPurchase::with(['client', 'membershipType']);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($onlyActive === true) {
            $today = now()->startOfDay()->toDateString();
            $query->where('starts_at', '<=', $today)
                  ->where(function ($q) use ($today) {
                      $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', $today);
                  })
                  ->where(function ($q) {
                      $q->whereNull('visits_left')
                        ->orWhere('visits_left', '>', 0);
                  });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?MembershipPurchase
    {
        return MembershipPurchase::with(['client', 'membershipType'])->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): MembershipPurchase
    {
        $purchase = MembershipPurchase::create($data);

        return $purchase->load(['client', 'membershipType']);
    }

    public function decrementVisit(MembershipPurchase $purchase): MembershipPurchase
    {
        if ($purchase->visits_left !== null && $purchase->visits_left > 0) {
            $purchase->decrement('visits_left');
        }

        return $purchase->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): MembershipPurchase
    {
        $purchase = $this->findById($id);
        if (!$purchase) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Purchase with ID $id not found");
        }

        $purchase->update($data);
        return $purchase->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): void
    {
        $purchase = $this->findById($id);
        if ($purchase) {
            $purchase->delete();
        }
    }
}
