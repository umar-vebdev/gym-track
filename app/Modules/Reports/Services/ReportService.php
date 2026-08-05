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
     * @param string|null $type
     *
     * @return array<string, mixed>
     */
    public function getRevenueReport(?string $startDate = null, ?string $endDate = null, ?string $type = null): array
    {
        $start = $startDate ?? now()->startOfMonth()->toDateString();
        $end = $endDate ?? now()->toDateString();

        return $this->repository->getRevenueReport($start, $end, $type);
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
     * История событий.
     */
    public function getHistory(?string $startDate = null, ?string $endDate = null, ?string $type = null, int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getHistory($startDate, $endDate, $type, $page, $perPage);
    }
}
