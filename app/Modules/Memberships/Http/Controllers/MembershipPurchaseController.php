<?php

namespace App\Modules\Memberships\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Memberships\Http\Requests\StoreMembershipPurchaseRequest;
use App\Modules\Memberships\Http\Resources\MembershipPurchaseResource;
use App\Modules\Memberships\Services\MembershipPurchaseService;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Продажи абонементов
 */
class MembershipPurchaseController extends Controller
{
    /**
     * @param MembershipPurchaseService $service
     */
    public function __construct(
        private readonly MembershipPurchaseService $service
    ) {}

    /**
     * История продаж абонементов
     *
     * Возвращает список проданных абонементов с пагинацией и фильтрами.
     *
     * @queryParam int client_id Фильтр по ID клиента. Example: 1
     * @queryParam bool active_only Фильтр по только активным абонементам. Example: true
     * @queryParam int per_page Количество записей на страницу. Example: 15
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "client_id": 1,
     *         "membership_type_id": 1,
     *         "amount_paid": 2500.0,
     *         "starts_at": "2026-08-01",
     *         "expires_at": "2026-08-30",
     *         "visits_left": null,
     *         "payment_method": "cash",
     *         "is_active": true,
     *         "created_at": "2026-08-01T00:00:00.000000Z",
     *         "updated_at": "2026-08-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "meta": {
     *       "pagination": {
     *         "current_page": 1,
     *         "per_page": 15,
     *         "total": 1,
     *         "last_page": 1,
     *         "from": 1,
     *         "to": 1
     *       }
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $request->query('client_id') ? (int) $request->query('client_id') : null;
        $onlyActive = $request->has('active_only') ? $request->boolean('active_only') : null;
        $perPage = (int) $request->query('per_page', 15);

        $purchases = $this->service->list($clientId, $onlyActive, $perPage);

        return ApiResponse::make()->success(
            MembershipPurchaseResource::collection($purchases)
        );
    }

    /**
     * Детали проданного абонемента
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "membership_type_id": 1,
     *     "amount_paid": 2500.0,
     *     "starts_at": "2026-08-01",
     *     "expires_at": "2026-08-30",
     *     "visits_left": null,
     *     "payment_method": "cash",
     *     "is_active": true,
     *     "created_at": "2026-08-01T00:00:00.000000Z",
     *     "updated_at": "2026-08-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": {
     *     "code": "NOT_FOUND",
     *     "message": "Запись о покупке не найдена",
     *     "details": []
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $purchase = $this->service->findById($id);

        if (!$purchase) {
            return ApiResponse::make()->notFound('Запись о покупке не найдена');
        }

        return ApiResponse::make()->success(new MembershipPurchaseResource($purchase));
    }

    /**
     * Продать абонемент клиенту
     *
     * Цифровой аналог оформления подписки/покупки в тетради.
     * Дата окончания (`expires_at`) или остаток визитов (`visits_left`) рассчитываются автоматически.
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "membership_type_id": 1,
     *     "amount_paid": 2500.0,
     *     "starts_at": "2026-08-01",
     *     "expires_at": "2026-08-30",
     *     "visits_left": null,
     *     "payment_method": "cash",
     *     "is_active": true,
     *     "created_at": "2026-08-01T00:00:00.000000Z",
     *     "updated_at": "2026-08-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "error": {
     *     "code": "VALIDATION_ERROR",
     *     "message": "Ошибка валидации данных",
     *     "details": {
     *       "membership_type_id": ["Указанный тип абонемента недоступен для покупки"]
     *     }
     *   }
     * }
     */
    public function store(StoreMembershipPurchaseRequest $request): JsonResponse
    {
        try {
            $purchase = $this->service->purchase($request->validated());

            return ApiResponse::make()->created(new MembershipPurchaseResource($purchase));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::make()->error($e->getMessage(), 'INVALID_MEMBERSHIP_TYPE', [], 422);
        }
    }
}
