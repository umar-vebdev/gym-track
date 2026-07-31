<?php

namespace App\Modules\Clients\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clients\Http\Requests\StoreClientRequest;
use App\Modules\Clients\Http\Requests\UpdateClientRequest;
use App\Modules\Clients\Http\Resources\ClientResource;
use App\Modules\Clients\Services\ClientService;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Клиенты
 */
class ClientController extends Controller
{
    /**
     * @param ClientService $service
     */
    public function __construct(
        private readonly ClientService $service
    ) {}

    /**
     * Список клиентов
     *
     * Возвращает список клиентов с пагинацией.
     * Поддерживает поиск по ФИО, телефону и коду клиента через параметр `search`.
     *
     * @queryParam string search Поиск по ФИО, телефону или коду. Example: Иванов
     * @queryParam int per_page Кол-во записей на страницу. Example: 15
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "client_code": "GT-0001",
     *         "full_name": "Иванов Иван Иванович",
     *         "phone": "+7 999 123-45-67",
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
        $clients = $this->service->list(
            search: $request->query('search'),
            perPage: (int) $request->query('per_page', 15)
        );

        return ApiResponse::make()->success(
            ClientResource::collection($clients)
        );
    }

    /**
     * Карточка клиента
     *
     * Возвращает детальную информацию о клиенте по ID.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_code": "GT-0001",
     *     "full_name": "Иванов Иван Иванович",
     *     "phone": "+7 999 123-45-67",
     *     "created_at": "2026-08-01T00:00:00.000000Z",
     *     "updated_at": "2026-08-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": {
     *     "code": "NOT_FOUND",
     *     "message": "Клиент не найден",
     *     "details": []
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $client = $this->service->findById($id);

        if (!$client) {
            return ApiResponse::make()->notFound('Клиент не найден');
        }

        return ApiResponse::make()->success(new ClientResource($client));
    }

    /**
     * Добавить клиента
     *
     * Создаёт нового клиента (посетителя зала).
     * Код клиента (client_code) генерируется автоматически.
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_code": "GT-0001",
     *     "full_name": "Иванов Иван Иванович",
     *     "phone": "+7 999 123-45-67",
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
     *       "phone": ["Клиент с таким телефоном уже существует"]
     *     }
     *   }
     * }
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->service->create($request->validated());

        return ApiResponse::make()->created(new ClientResource($client));
    }

    /**
     * Обновить клиента
     *
     * Обновляет данные существующего клиента.
     * Можно передать только изменяемые поля. Код клиента не изменяется.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "client_code": "GT-0001",
     *     "full_name": "Петров Пётр Петрович",
     *     "phone": "+7 999 987-65-43",
     *     "created_at": "2026-08-01T00:00:00.000000Z",
     *     "updated_at": "2026-08-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": {
     *     "code": "NOT_FOUND",
     *     "message": "Клиент не найден",
     *     "details": []
     *   }
     * }
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = $this->service->findById($id);

        if (!$client) {
            return ApiResponse::make()->notFound('Клиент не найден');
        }

        $client = $this->service->update($client, $request->validated());

        return ApiResponse::make()->success(new ClientResource($client));
    }

    /**
     * Удалить клиента
     *
     * Удаляет клиента из базы данных.
     *
     * @response 204
     *
     * @response 404 {
     *   "success": false,
     *   "error": {
     *     "code": "NOT_FOUND",
     *     "message": "Клиент не найден",
     *     "details": []
     *   }
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        $client = $this->service->findById($id);

        if (!$client) {
            return ApiResponse::make()->notFound('Клиент не найден');
        }

        $this->service->delete($client);

        return ApiResponse::make()->noContent();
    }
}
