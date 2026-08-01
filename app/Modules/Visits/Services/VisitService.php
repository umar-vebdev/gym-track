<?php

namespace App\Modules\Visits\Services;

use App\Modules\Clients\Repositories\ClientRepositoryInterface;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Memberships\Repositories\MembershipPurchaseRepositoryInterface;
use App\Modules\Visits\Models\Visit;
use App\Modules\Visits\Repositories\VisitRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Сервис учета посещений и фиксации чек-инов.
 */
class VisitService
{
    /**
     * @param VisitRepositoryInterface              $visitRepository
     * @param MembershipPurchaseRepositoryInterface $purchaseRepository
     * @param ClientRepositoryInterface             $clientRepository
     */
    public function __construct(
        private readonly VisitRepositoryInterface $visitRepository,
        private readonly MembershipPurchaseRepositoryInterface $purchaseRepository,
        private readonly ClientRepositoryInterface $clientRepository
    ) {}

    /**
     * Список визитов.
     *
     * @param int|null    $clientId
     * @param string|null $date
     * @param int         $perPage
     *
     * @return LengthAwarePaginator
     */
    public function list(?int $clientId = null, ?string $date = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->visitRepository->paginate($clientId, $date, $perPage);
    }

    /**
     * Зафиксировать визит (Чек-ин клиента).
     *
     * Проверяет наличие и активность абонемента у клиента.
     * Если абонемент по визитам — автоматически списывает 1 посещение.
     *
     * @param int      $clientId             ID клиента
     * @param int|null $membershipPurchaseId Опциональный ID конкретного абонемента
     *
     * @return Visit
     *
     * @throws InvalidArgumentException если клиент не найден или нет активного абонемента
     */
    public function checkIn(int $clientId, ?int $membershipPurchaseId = null): Visit
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new InvalidArgumentException('Клиент не найден');
        }

        $purchase = null;

        if ($membershipPurchaseId) {
            $purchase = $this->purchaseRepository->findById($membershipPurchaseId);

            if (!$purchase || $purchase->client_id !== $clientId) {
                throw new InvalidArgumentException('Указанный абонемент не принадлежит клиенту');
            }

            if (!$purchase->isActive()) {
                throw new InvalidArgumentException('Указанный абонемент неактивен или его срок/визиты истекли');
            }
        } else {
            // Ищем первый активный абонемент у клиента
            $activePurchases = $this->purchaseRepository
                ->paginate(clientId: $clientId, onlyActive: true, perPage: 100);

            /** @var MembershipPurchase|null $purchase */
            $purchase = $activePurchases->first(fn (MembershipPurchase $p) => $p->isActive());

            if (!$purchase) {
                throw new InvalidArgumentException('У клиента нет активного абонемента для посещения');
            }
        }

        // Фиксируем визит
        $visit = $this->visitRepository->create([
            'client_id'              => $clientId,
            'membership_purchase_id' => $purchase->id,
            'visited_at'             => now(),
        ]);

        // Если абонемент с лимитом посещений — списываем 1 визит
        if ($purchase->visits_left !== null) {
            $this->purchaseRepository->decrementVisit($purchase);
        }

        return $visit;
    }

    /**
     * Найти визит по ID.
     *
     * @param int $id
     *
     * @return Visit|null
     */
    public function findById(int $id): ?Visit
    {
        return $this->visitRepository->findById($id);
    }

    /**
     * Отменить визит.
     *
     * @param Visit $visit
     *
     * @return bool
     */
    public function delete(Visit $visit): bool
    {
        return $this->visitRepository->delete($visit);
    }
}
