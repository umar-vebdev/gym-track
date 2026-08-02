<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\ReportRepositoryInterface;

/**
 * Сервис отчётов и аналитики.
 */
class ReportService
{
    /**
     * @param ReportRepositoryInterface $repository
     */
    public function __construct(
        private readonly ReportRepositoryInterface $repository
    ) {}

    /**
     * Сводные показатели дашборда.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array<string, mixed>
     */
    public function getDashboardOverview(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ?? now()->toDateString();
        $end = $endDate ?? now()->toDateString();

        return $this->repository->getDashboardOverview($start, $end);
    }

    /**
     * Отчёт по выручке.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array<string, mixed>
     */
    public function getRevenueReport(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ?? now()->startOfMonth()->toDateString();
        $end = $endDate ?? now()->toDateString();

        return $this->repository->getRevenueReport($start, $end);
    }

    /**
     * Отчёт по визитам.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array<string, mixed>
     */
    public function getVisitsReport(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ?? now()->startOfMonth()->toDateString();
        $end = $endDate ?? now()->toDateString();

        return $this->repository->getVisitsReport($start, $end);
    }

    /**
     * Истекающие абонементы.
     *
     * @param int $days
     *
     * @return array<int, mixed>
     */
    public function getExpiringMemberships(int $days = 7): array
    {
        return $this->repository->getExpiringMemberships($days);
    }

    /**
     * Список финансовых транзакций (продаж абонементов).
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $paymentMethod
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactions(?string $startDate = null, ?string $endDate = null, ?string $paymentMethod = null, int $perPage = 15)
    {
        return $this->repository->getTransactions($startDate, $endDate, $paymentMethod, $perPage);
    }
}
