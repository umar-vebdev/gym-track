<?php

namespace App\Modules\Reports\Repositories;

/**
 * Контракт репозитория отчётов и аналитики.
 */
interface ReportRepositoryInterface
{
    /**
     * Сводная статистика для дашборда за указанный период.
     *
     * @param string $startDate Дата начала YYYY-MM-DD
     * @param string $endDate   Дата окончания YYYY-MM-DD
     *
     * @return array<string, mixed>
     */
    public function getDashboardOverview(string $startDate, string $endDate): array;

    /**
     * Отчёт по выручке и продажам за период.
     *
     * @param string $startDate Дата начала YYYY-MM-DD
     * @param string $endDate   Дата окончания YYYY-MM-DD
     * @param string|null $type Тип продаж (membership, product)
     *
     * @return array<string, mixed>
     */
    public function getRevenueReport(string $startDate, string $endDate, ?string $type = null): array;

    /**
     * Отчёт по посещаемости за период (с расчётом пиковых часов).
     *
     * @param string $startDate Дата начала YYYY-MM-DD
     * @param string $endDate   Дата окончания YYYY-MM-DD
     *
     * @return array<string, mixed>
     */
    public function getVisitsReport(string $startDate, string $endDate): array;

    /**
     * Список клиентов, у которых абонемент истекает в ближайшие N дней.
     *
     * @param int $days Дней до истечения
     *
     * @return array<int, mixed>
     */
    public function getExpiringMemberships(int $days = 7): array;

    /**
     * История действий (посещения, покупки товаров, абонементов).
     */
    public function getHistory(?string $startDate = null, ?string $endDate = null, ?string $type = null, int $page = 1, int $perPage = 15): array;
}
