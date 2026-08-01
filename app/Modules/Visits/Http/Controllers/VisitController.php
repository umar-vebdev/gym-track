<?php

namespace App\Modules\Visits\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Responses\ApiResponse;
use App\Modules\Visits\Http\Requests\StoreVisitRequest;
use App\Modules\Visits\Http\Resources\VisitResource;
use App\Modules\Visits\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Посещения (Чек-ин)
 */
class VisitController extends Controller
{
    /**
     * @param VisitService $service
     */
    public function __construct(
        private readonly VisitService $service
    ) {}

    /**
     * История посещений
     *
     * Возвращает список всех зафиксированных визитов с пагинацией и фильтрацией.
     *
     * @queryParam int client_id Фильтр по клиенту. Example: 1
     * @queryParam string date Фильтр по дате (YYYY-MM-DD). Example: 2026-08-01
     * @queryParam int per_page Кол-во записей на страницу. Example: 15
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "client_id": 1,
     *         "membership_purchase_id": 1,
     *         "visited_at": "2026-08-01T15:30:00.000000Z",
     *         "created_at": "2026-08-01T15:30:00.000000Z",
     *         "updated_at": "2026-08-01T15:30:00.000000Z"
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
        $date = $request->query('date');
        $perPage = (int) $request->query('per_page', 15);

        $visits = $this->service->list($clientId, $date, $perPage);

        return ApiResponse::make()->success(
            VisitResource::collection($visits)
        );
    }

    /**
     * Чек-ин клиента (Зафиксировать визит)
     *
     * Регистрирует посещение зала. Проверяет активность абонемента.
     * Если у абонемента лимит визитов, автоматически уменьшает остаток на 1.
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "membership_purchase_id": 1,
     *     "visited_at": "2026-08-01T15:30:00.000000Z",
     *     "created_at": "2026-08-01T15:30:00.000000Z",
     *     "updated_at": "2026-08-01T15:30:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "error": {
     *     "code": "NO_ACTIVE_MEMBERSHIP",
     *     "message": "У клиента нет активного абонемента для посещения",
     *     "details": []
     *   }
     * }
     */
    public function store(StoreVisitRequest $request): JsonResponse
    {
        try {
            $visit = $this->service->checkIn(
                clientId: (int) $request->validated('client_id'),
                membershipPurchaseId: $request->validated('membership_purchase_id')
                    ? (int) $request->validated('membership_purchase_id')
                    : null
            );

            return ApiResponse::make()->created(new VisitResource($visit));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::make()->error(
                message: $e->getMessage(),
                code: 'CHECK_IN_FAILED',
                details: [],
                status: 422
            );
        }
    }

    /**
     * Просмотр визита
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_id": 1,
     *     "membership_purchase_id": 1,
     *     "visited_at": "2026-08-01T15:30:00.000000Z",
     *     "created_at": "2026-08-01T15:30:00.000000Z",
     *     "updated_at": "2026-08-01T15:30:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": {
     *     "code": "NOT_FOUND",
     *     "message": "Визит не найден",
     *     "details": []
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $visit = $this->service->findById($id);

        if (!$visit) {
            return ApiResponse::make()->notFound('Визит не найден');
        }

        return ApiResponse::make()->success(new VisitResource($visit));
    }

    /**
     * Отменить/удалить визит
     *
     * @response 204
     */
    public function destroy(int $id): JsonResponse
    {
        $visit = $this->service->findById($id);

        if (!$visit) {
            return ApiResponse::make()->notFound('Визит не найден');
        }

        $this->service->delete($visit);

        return ApiResponse::make()->noContent();
    }
}
