<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Visits\Models\Visit;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent-реализация агрегирующих запросов для отчётов.
 */
class EloquentReportRepository implements ReportRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDashboardOverview(string $startDate, string $endDate): array
    {
        $todayRevenue = (float) MembershipPurchase::query()
            ->whereBetween(DB::raw('DATE(membership_purchases.created_at)'), [$startDate, $endDate])
            ->sum('membership_purchases.amount_paid');

        $todayPurchasesCount = MembershipPurchase::query()
            ->whereBetween(DB::raw('DATE(membership_purchases.created_at)'), [$startDate, $endDate])
            ->count();

        $todayVisitsCount = Visit::query()
            ->whereBetween(DB::raw('DATE(visits.visited_at)'), [$startDate, $endDate])
            ->count();

        $totalClientsCount = Client::query()->count();

        $activePurchasesCount = MembershipPurchase::query()
            ->where('membership_purchases.starts_at', '<=', $endDate)
            ->where(function ($q) use ($endDate) {
                $q->whereNull('membership_purchases.expires_at')
                  ->orWhere('membership_purchases.expires_at', '>=', $endDate);
            })
            ->where(function ($q) {
                $q->whereNull('membership_purchases.visits_left')
                  ->orWhere('membership_purchases.visits_left', '>', 0);
            })
            ->count();

        return [
            'date'                  => $endDate,
            'today_revenue'         => $todayRevenue,
            'today_purchases_count' => $todayPurchasesCount,
            'today_visits_count'    => $todayVisitsCount,
            'total_clients_count'   => $totalClientsCount,
            'active_memberships'    => $activePurchasesCount,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRevenueReport(string $startDate, string $endDate): array
    {
        $query = MembershipPurchase::query()
            ->whereBetween(DB::raw('DATE(membership_purchases.created_at)'), [$startDate, $endDate]);

        $totalRevenue = (float) (clone $query)->sum('membership_purchases.amount_paid');
        $totalCount = (int) (clone $query)->count();

        // Группировка по способам оплаты
        $byPaymentMethod = (clone $query)
            ->select(
                'membership_purchases.payment_method',
                DB::raw('SUM(membership_purchases.amount_paid) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('membership_purchases.payment_method')
            ->get()
            ->map(fn ($row) => [
                'payment_method' => $row->payment_method ?? 'cash',
                'total'          => (float) $row->total,
                'count'          => (int) $row->count,
            ])
            ->toArray();

        // Группировка по типам абонементов
        $byMembershipType = (clone $query)
            ->join('membership_types', 'membership_purchases.membership_type_id', '=', 'membership_types.id')
            ->select(
                'membership_types.id',
                'membership_types.name',
                DB::raw('SUM(membership_purchases.amount_paid) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('membership_types.id', 'membership_types.name')
            ->get()
            ->map(fn ($row) => [
                'type_id'   => (int) $row->id,
                'type_name' => $row->name,
                'total'     => (float) $row->total,
                'count'     => (int) $row->count,
            ])
            ->toArray();

        // Динамика по дням
        $daily = (clone $query)
            ->select(
                DB::raw('DATE(membership_purchases.created_at) as date'),
                DB::raw('SUM(membership_purchases.amount_paid) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(membership_purchases.created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'  => $row->date,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ])
            ->toArray();

        return [
            'start_date'         => $startDate,
            'end_date'           => $endDate,
            'total_revenue'      => $totalRevenue,
            'total_purchases'    => $totalCount,
            'by_payment_method'  => $byPaymentMethod,
            'by_membership_type' => $byMembershipType,
            'daily'              => $daily,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getVisitsReport(string $startDate, string $endDate): array
    {
        $query = Visit::query()
            ->whereBetween(DB::raw('DATE(visits.visited_at)'), [$startDate, $endDate]);

        $totalVisits = (int) (clone $query)->count();

        // Визиты по дням
        $daily = (clone $query)
            ->select(DB::raw('DATE(visits.visited_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(visits.visited_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'  => $row->date,
                'count' => (int) $row->count,
            ])
            ->toArray();

        // Загруженность по часам (пиковые часы)
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $hourExpr = $isSqlite ? "strftime('%H', visits.visited_at)" : "HOUR(visits.visited_at)";

        $hourly = (clone $query)
            ->select(DB::raw("{$hourExpr} as hour"), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw($hourExpr))
            ->orderBy('hour')
            ->get()
            ->map(fn ($row) => [
                'hour'  => sprintf('%02d:00', (int) $row->hour),
                'count' => (int) $row->count,
            ])
            ->toArray();

        return [
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'total_visits' => $totalVisits,
            'daily'        => $daily,
            'hourly_peaks' => $hourly,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiringMemberships(int $days = 7): array
    {
        $today = now()->startOfDay()->toDateString();
        $targetDate = now()->addDays($days)->endOfDay()->toDateString();

        return MembershipPurchase::with(['client', 'membershipType'])
            ->whereNotNull('membership_purchases.expires_at')
            ->whereBetween('membership_purchases.expires_at', [$today, $targetDate])
            ->orderBy('membership_purchases.expires_at')
            ->get()
            ->map(fn (MembershipPurchase $p) => [
                'purchase_id'     => $p->id,
                'client'          => [
                    'id'          => $p->client->id,
                    'client_code' => $p->client->client_code,
                    'full_name'   => $p->client->full_name,
                    'phone'       => $p->client->phone,
                ],
                'membership_type' => $p->membershipType->name,
                'expires_at'      => $p->expires_at?->format('Y-m-d'),
                'days_left'       => (int) now()->diffInDays($p->expires_at, false),
            ])
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactions(?string $startDate, ?string $endDate, ?string $paymentMethod = null, int $perPage = 15)
    {
        $query = MembershipPurchase::with(['client', 'membershipType'])
            ->orderByDesc('created_at');

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(membership_purchases.created_at)'), [$startDate, $endDate]);
        }
        
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        return $query->paginate($perPage);
    }
}
