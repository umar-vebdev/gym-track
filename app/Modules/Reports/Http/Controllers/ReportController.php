<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Services\ReportService;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Отчёты и Аналитика
 */
class ReportController extends Controller
{
    /**
     * @param ReportService $service
     */
    public function __construct(
        private readonly ReportService $service
    ) {}

    /**
     * Дашборд (Главная статистика)
     *
     * Возвращает сводные ключевые показатели за указанный период.
     *
     * @queryParam string start_date Дата начала (YYYY-MM-DD). По умолчанию сегодня. 
     * @queryParam string end_date Дата окончания (YYYY-MM-DD). По умолчанию сегодня.
     *
     * @response 200 {
     *   "success": true,
     *   ...
     * }
     */
    public function dashboard(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        
        $overview = $this->service->getDashboardOverview($startDate, $endDate);

        return ApiResponse::make()->success($overview);
    }

    /**
     * Отчёт по выручке
     *
     * Возвращает полную аналитику финансовой выручки за указанный период (сумма, количество продаж, разбивка по способам оплаты, по тарифам и динамика по дням).
     *
     * @queryParam string start_date Дата начала (YYYY-MM-DD). По умолчанию начало текущего месяца. Example: 2026-08-01
     * @queryParam string end_date Дата окончания (YYYY-MM-DD). По умолчанию сегодня. Example: 2026-08-31
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "start_date": "2026-08-01",
     *     "end_date": "2026-08-31",
     *     "total_revenue": 55000.0,
     *     "total_purchases": 22,
     *     "by_payment_method": [
     *       {"payment_method": "cash", "total": 30000.0, "count": 12},
     *       {"payment_method": "card", "total": 25000.0, "count": 10}
     *     ],
     *     "by_membership_type": [
     *       {"type_id": 1, "type_name": "Месячный безлимит", "total": 45000.0, "count": 18}
     *     ],
     *     "daily": [
     *       {"date": "2026-08-01", "total": 5000.0, "count": 2}
     *     ]
     *   }
     * }
     */
    public function revenue(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $report = $this->service->getRevenueReport($startDate, $endDate);

        return ApiResponse::make()->success($report);
    }

    /**
     * Отчёт по посещаемости
     *
     * Аналитика визитов за выбранный период с распределением по дням и выявлением пиковых часов посещаемости зала.
     *
     * @queryParam string start_date Дата начала (YYYY-MM-DD). Example: 2026-08-01
     * @queryParam string end_date Дата окончания (YYYY-MM-DD). Example: 2026-08-31
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "start_date": "2026-08-01",
     *     "end_date": "2026-08-31",
     *     "total_visits": 340,
     *     "daily": [
     *       {"date": "2026-08-01", "count": 18}
     *     ],
     *     "hourly_peaks": [
     *       {"hour": "18:00", "count": 45},
     *       {"hour": "19:00", "count": 60}
     *     ]
     *   }
     * }
     */
    public function visits(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $report = $this->service->getVisitsReport($startDate, $endDate);

        return ApiResponse::make()->success($report);
    }

    /**
     * Истекающие абонементы
     *
     * Список абонементов клиентов, срок действия которых заканчивается в ближайшие N дней.
     * Полезно для формирования списков обзвона/напоминаний о продлении.
     *
     * @queryParam int days Дней до истечения (по умолчанию 7). Example: 7
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "purchase_id": 1,
     *       "client": {
     *         "id": 1,
     *         "client_code": "GT-0001",
     *         "full_name": "Иванов Иван Иванович",
     *         "phone": "+7 999 123-45-67"
     *       },
     *       "membership_type": "Месячный безлимит",
     *       "expires_at": "2026-08-05",
     *       "days_left": 4
     *     }
     *   ]
     * }
     */
    public function expiringMemberships(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 7);

        $list = $this->service->getExpiringMemberships($days);

        return ApiResponse::make()->success($list);
    }

    /**
     * Журнал финансовых транзакций (продаж)
     */
    public function transactions(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $paymentMethod = $request->query('payment_method');
        $perPage = (int) $request->query('per_page', 15);

        $transactions = $this->service->getTransactions($startDate, $endDate, $paymentMethod, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => \App\Modules\Memberships\Http\Resources\MembershipPurchaseResource::collection($transactions->items()),
                'meta' => [
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                        'last_page' => $transactions->lastPage()
                    ]
                ]
            ]
        ]);
    }
}
