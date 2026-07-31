<?php

namespace App\Modules\Memberships\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Memberships\Http\Requests\StoreMembershipTypeRequest;
use App\Modules\Memberships\Http\Requests\UpdateMembershipTypeRequest;
use App\Modules\Memberships\Http\Resources\MembershipTypeResource;
use App\Modules\Memberships\Services\MembershipTypeService;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Тарифы абонементов
 */
class MembershipTypeController extends Controller
{
    /**
     * @param MembershipTypeService $service
     */
    public function __construct(
        private readonly MembershipTypeService $service
    ) {}

    /**
     * Список тарифов
     *
     * Возвращает список всех типов абонементов.
     * По умолчанию возвращаются только активные тарифы.
     *
     * @queryParam bool all Возвращать ли все тарифы, включая неактивные. Example: false
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Месячный безлимит",
     *       "duration_type": "days",
     *       "duration_value": 30,
     *       "price": 2500.0,
     *       "is_active": true,
     *       "created_at": "2026-08-01T00:00:00.000000Z",
     *       "updated_at": "2026-08-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $onlyActive = !$request->boolean('all');
        $types = $this->service->list($onlyActive);

        return ApiResponse::make()->success(
            MembershipTypeResource::collection($types)
        );
    }

    /**
     * Просмотр тарифа
     *
     * Возвращает детали типа абонемента по ID.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Месячный безлимит",
     *     "duration_type": "days",
     *     "duration_value": 30,
     *     "price": 2500.0,
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
     *     "message": "Тариф не найден",
     *     "details": []
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $type = $this->service->findById($id);

        if (!$type) {
            return ApiResponse::make()->notFound('Тариф не найден');
        }

        return ApiResponse::make()->success(new MembershipTypeResource($type));
    }

    /**
     * Создать тариф
     *
     * Добавляет новый тип абонемента в систему.
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "12 визитов",
     *     "duration_type": "visits",
     *     "duration_value": 12,
     *     "price": 2000.0,
     *     "is_active": true,
     *     "created_at": "2026-08-01T00:00:00.000000Z",
     *     "updated_at": "2026-08-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function store(StoreMembershipTypeRequest $request): JsonResponse
    {
        $type = $this->service->create($request->validated());

        return ApiResponse::make()->created(new MembershipTypeResource($type));
    }

    /**
     * Обновить тариф
     *
     * Изменяет свойства имеющегося типа абонемента.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "12 визитов (акция)",
     *     "duration_type": "visits",
     *     "duration_value": 12,
     *     "price": 1800.0,
     *     "is_active": true,
     *     "created_at": "2026-08-01T00:00:00.000000Z",
     *     "updated_at": "2026-08-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function update(UpdateMembershipTypeRequest $request, int $id): JsonResponse
    {
        $type = $this->service->findById($id);

        if (!$type) {
            return ApiResponse::make()->notFound('Тариф не найден');
        }

        $type = $this->service->update($type, $request->validated());

        return ApiResponse::make()->success(new MembershipTypeResource($type));
    }

    /**
     * Переключить активность тарифа
     *
     * Деактивирует или активирует тариф. Снятый с продажи тариф остаётся в истории покупок.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Старый тариф",
     *     "is_active": false
     *   }
     * }
     */
    public function toggle(int $id): JsonResponse
    {
        $type = $this->service->findById($id);

        if (!$type) {
            return ApiResponse::make()->notFound('Тариф не найден');
        }

        $type = $this->service->toggleActive($type);

        return ApiResponse::make()->success(new MembershipTypeResource($type));
    }
}
