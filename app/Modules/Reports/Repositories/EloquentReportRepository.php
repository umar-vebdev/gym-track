<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use App\Modules\Products\Models\ProductSale;
use App\Modules\Visits\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-реализация агрегирующих запросов для отчётов.
 */
class EloquentReportRepository implements ReportRepositoryInterface
{
    public function getDashboardOverview(string $startDate, string $endDate): array
    {
        $todayRevenue = (float) MembershipPurchase::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->sum('amount_paid');
            
        $todayRevenue += (float) ProductSale::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->sum('total_price');

        $todayPurchasesCount = MembershipPurchase::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->count();
            
        $todayPurchasesCount += ProductSale::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->count();

        $todayVisitsCount = Visit::query()
            ->whereBetween(DB::raw('DATE(visited_at)'), [$startDate, $endDate])
            ->count();

        $totalClientsCount = Client::query()->count();

        $activePurchasesCount = MembershipPurchase::query()
            ->where('starts_at', '<=', $endDate)
            ->where(function ($q) use ($endDate) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', $endDate);
            })
            ->where(function ($q) {
                $q->whereNull('visits_left')
                  ->orWhere('visits_left', '>', 0);
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

    public function getRevenueReport(string $startDate, string $endDate, ?string $type = null): array
    {
        $totalRevenue = 0;
        $totalCount = 0;
        $byPaymentMethod = [];
        $daily = [];

        if (!$type || $type === 'membership') {
            $query = MembershipPurchase::query()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            $totalRevenue += (float) (clone $query)->sum('amount_paid');
            $totalCount += (int) (clone $query)->count();
            
            $methods = (clone $query)->select('payment_method', DB::raw('SUM(amount_paid) as sum'), DB::raw('COUNT(*) as c'))
                ->groupBy('payment_method')->get();
            foreach ($methods as $m) {
                $method = $m->payment_method ?? 'cash';
                if (!isset($byPaymentMethod[$method])) $byPaymentMethod[$method] = ['payment_method' => $method, 'total' => 0, 'count' => 0];
                $byPaymentMethod[$method]['total'] += (float) $m->sum;
                $byPaymentMethod[$method]['count'] += (int) $m->c;
            }

            $days = (clone $query)->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount_paid) as sum'), DB::raw('COUNT(*) as c'))
                ->groupBy(DB::raw('DATE(created_at)'))->get();
            foreach ($days as $d) {
                if (!isset($daily[$d->date])) $daily[$d->date] = ['date' => $d->date, 'total' => 0, 'count' => 0];
                $daily[$d->date]['total'] += (float) $d->sum;
                $daily[$d->date]['count'] += (int) $d->c;
            }
        }

        if (!$type || $type === 'product') {
            $query = ProductSale::query()->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            $totalRevenue += (float) (clone $query)->sum('total_price');
            $totalCount += (int) (clone $query)->count();

            $methods = (clone $query)->select('payment_method', DB::raw('SUM(total_price) as sum'), DB::raw('COUNT(*) as c'))
                ->groupBy('payment_method')->get();
            foreach ($methods as $m) {
                $method = $m->payment_method ?? 'cash';
                if (!isset($byPaymentMethod[$method])) $byPaymentMethod[$method] = ['payment_method' => $method, 'total' => 0, 'count' => 0];
                $byPaymentMethod[$method]['total'] += (float) $m->sum;
                $byPaymentMethod[$method]['count'] += (int) $m->c;
            }

            $days = (clone $query)->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as sum'), DB::raw('COUNT(*) as c'))
                ->groupBy(DB::raw('DATE(created_at)'))->get();
            foreach ($days as $d) {
                if (!isset($daily[$d->date])) $daily[$d->date] = ['date' => $d->date, 'total' => 0, 'count' => 0];
                $daily[$d->date]['total'] += (float) $d->sum;
                $daily[$d->date]['count'] += (int) $d->c;
            }
        }

        usort($daily, fn($a, $b) => strcmp($a['date'], $b['date']));

        return [
            'start_date'         => $startDate,
            'end_date'           => $endDate,
            'total_revenue'      => $totalRevenue,
            'total_purchases'    => $totalCount,
            'type'               => $type,
            'by_payment_method'  => array_values($byPaymentMethod),
            'daily'              => array_values($daily),
        ];
    }

    public function getVisitsReport(string $startDate, string $endDate): array
    {
        $query = Visit::query()
            ->whereBetween(DB::raw('DATE(visits.visited_at)'), [$startDate, $endDate]);

        $totalVisits = (int) (clone $query)->count();

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

    public function getHistory(?string $startDate = null, ?string $endDate = null, ?string $type = null, int $page = 1, int $perPage = 15): array
    {
        $events = collect();

        if (!$type || $type === 'membership') {
            $q = MembershipPurchase::with(['client', 'membershipType']);
            if ($startDate) $q->whereDate('created_at', '>=', $startDate);
            if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            $events = $events->merge($q->get()->map(fn($item) => (object)[
                'type' => 'membership_purchase', 
                'created_at' => $item->created_at, 
                'data' => $item
            ]));
        }

        if (!$type || $type === 'visit') {
            $q = Visit::with(['client']);
            if ($startDate) $q->whereDate('created_at', '>=', $startDate);
            if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            $events = $events->merge($q->get()->map(fn($item) => (object)[
                'type' => 'visit', 
                'created_at' => $item->created_at, 
                'data' => $item
            ]));
        }

        if (!$type || $type === 'product') {
            $q = ProductSale::with(['client', 'product']);
            if ($startDate) $q->whereDate('created_at', '>=', $startDate);
            if ($endDate) $q->whereDate('created_at', '<=', $endDate);
            $events = $events->merge($q->get()->map(fn($item) => (object)[
                'type' => 'product_sale', 
                'created_at' => $item->created_at, 
                'data' => $item
            ]));
        }

        $events = $events->sortByDesc('created_at')->values();

        // Paginate manually
        $total = $events->count();
        $items = $events->slice(($page - 1) * $perPage, $perPage)->values();

        return [
            'items' => $items,
            'meta' => [
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ]
            ]
        ];
    }
}
