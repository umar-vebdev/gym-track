<?php

namespace App\Modules\Memberships\Services;

use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Memberships\Models\MembershipType;
use App\Modules\Memberships\Repositories\MembershipPurchaseRepositoryInterface;
use App\Modules\Memberships\Repositories\MembershipTypeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Сервис оформления и управления продажами абонементов.
 */
class MembershipPurchaseService
{
    /**
     * @param MembershipPurchaseRepositoryInterface $purchaseRepository
     * @param MembershipTypeRepositoryInterface     $typeRepository
     */
    public function __construct(
        private readonly MembershipPurchaseRepositoryInterface $purchaseRepository,
        private readonly MembershipTypeRepositoryInterface $typeRepository
    ) {}

    /**
     * Получить список продаж.
     *
     * @param int|null  $clientId
     * @param bool|null $onlyActive
     * @param int       $perPage
     *
     * @return LengthAwarePaginator
     */
    public function list(?int $clientId = null, ?bool $onlyActive = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchaseRepository->paginate($clientId, $onlyActive, $perPage);
    }

    /**
     * Оформить продажу абонемента клиенту.
     *
     * Автоматически рассчитывает expires_at или visits_left в зависимости от duration_type тарифа.
     *
     * @param array<string, mixed> $data
     *
     * @return MembershipPurchase
     *
     * @throws InvalidArgumentException если тип абонемента не найден или не активен
     */
    public function purchase(array $data): MembershipPurchase
    {
        /** @var MembershipType|null $type */
        $type = $this->typeRepository->findById((int) $data['membership_type_id']);

        if (!$type || !$type->is_active) {
            throw new InvalidArgumentException('Указанный тип абонемента недоступен для покупки');
        }

        $startsAt = Carbon::parse($data['starts_at'] ?? now());

        $purchaseData = [
            'client_id'          => $data['client_id'],
            'membership_type_id' => $type->id,
            'amount_paid'        => $data['amount_paid'] ?? $type->price,
            'starts_at'          => $startsAt->toDateString(),
            'payment_method'     => $data['payment_method'] ?? 'cash',
            'expires_at'         => null,
            'visits_left'        => null,
        ];

        if ($type->duration_type === 'days') {
            // Расчёт даты окончания: starts_at + duration_value дней - 1 день
            $purchaseData['expires_at'] = $startsAt->copy()
                ->addDays($type->duration_value)
                ->subDay()
                ->toDateString();
        } elseif ($type->duration_type === 'visits') {
            $purchaseData['visits_left'] = $type->duration_value;
        }

        return $this->purchaseRepository->create($purchaseData);
    }

    /**
     * Найти покупку по ID.
     *
     * @param int $id
     *
     * @return MembershipPurchase|null
     */
    public function findById(int $id): ?MembershipPurchase
    {
        return $this->purchaseRepository->findById($id);
    }
    
    /**
     * Обновить покупку абонемента.
     *
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return MembershipPurchase
     */
    public function update(int $id, array $data): MembershipPurchase
    {
        return $this->purchaseRepository->update($id, $data);
    }
    
    /**
     * Удалить покупку абонемента.
     *
     * @param int $id
     *
     * @return void
     */
    public function delete(int $id): void
    {
        $this->purchaseRepository->delete($id);
    }
}
