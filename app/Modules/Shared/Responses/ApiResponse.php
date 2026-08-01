<?php

declare(strict_types=1);

namespace App\Modules\Shared\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Единый класс для формирования стандартизированных JSON-ответов API.
 *
 * Используется во всех контроллерах для единообразного формата ответов.
 *
 * Формат успешного ответа:
 * ```json
 * {
 *   "success": true,
 *   "data": { ... }
 * }
 * ```
 *
 * Формат ответа с ошибкой:
 * ```json
 * {
 *   "success": false,
 *   "error": {
 *     "code": "ERROR_CODE",
 *     "message": "Описание ошибки",
 *     "details": { ... }
 *   }
 * }
 * ```
 *
 * Формат ответа с пагинацией:
 * ```json
 * {
 *   "success": true,
 *   "data": {
 *     "items": [ ... ],
 *     "meta": {
 *       "pagination": {
 *         "current_page": 1,
 *         "per_page": 15,
 *         "total": 50,
 *         "last_page": 4,
 *         "from": 1,
 *         "to": 15
 *       }
 *     }
 *   }
 * }
 * ```
 */
class ApiResponse
{
    private bool $success = true;

    private int $statusCode = 200;

    private mixed $data = null;

    private string $errorCode = '';

    private string $errorMessage = '';

    /**
     * @var array<string, mixed>
     */
    private array $errorDetails = [];

    /**
     * Фабричный метод для fluent-синтаксиса.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Установить данные ответа.
     *
     * @param mixed $data Данные (Resource, массив, коллекция)
     *
     * @return static
     */
    public function withData(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Успешный ответ.
     *
     * @param mixed $data   Данные ответа
     * @param int   $status HTTP-код (по умолчанию 200)
     *
     * @return JsonResponse
     */
    public function success(mixed $data = null, int $status = 200): JsonResponse
    {
        $this->success = true;
        $this->statusCode = $status;
        $this->data = $data;

        return $this->send();
    }

    /**
     * Успешный ответ на создание ресурса (HTTP 201).
     *
     * @param mixed $data Данные созданного ресурса
     *
     * @return JsonResponse
     */
    public function created(mixed $data = null): JsonResponse
    {
        return $this->success($data, 201);
    }

    /**
     * Ответ без контента (HTTP 204).
     *
     * @return JsonResponse
     */
    public function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Ответ с ошибкой.
     *
     * @param string              $message Сообщение об ошибке
     * @param string              $code    Код ошибки (машиночитаемый)
     * @param array<string,mixed> $details Детали ошибки (например, ошибки валидации)
     * @param int                 $status  HTTP-код
     *
     * @return JsonResponse
     */
    public function error(
        string $message,
        string $code = 'ERROR',
        array $details = [],
        int $status = 400
    ): JsonResponse {
        $this->success = false;
        $this->statusCode = $status;
        $this->errorMessage = $message;
        $this->errorCode = $code;
        $this->errorDetails = $details;

        return $this->send();
    }

    /**
     * Ошибка валидации (HTTP 422).
     *
     * @param array<string, mixed> $errors Ошибки валидации по полям
     *
     * @return JsonResponse
     */
    public function validationError(array $errors): JsonResponse
    {
        return $this->error(
            message: __('api.validation_error'),
            code: 'VALIDATION_ERROR',
            details: $errors,
            status: 422
        );
    }

    /**
     * Ресурс не найден (HTTP 404).
     *
     * @param string|null $message Кастомное сообщение
     *
     * @return JsonResponse
     */
    public function notFound(?string $message = null): JsonResponse
    {
        $message = $message ?? __('api.resource_not_found');

        return $this->error($message, 'NOT_FOUND', [], 404);
    }

    /**
     * Не авторизован (HTTP 401).
     *
     * @param string|null $message Кастомное сообщение
     *
     * @return JsonResponse
     */
    public function unauthorized(?string $message = null): JsonResponse
    {
        $message = $message ?? __('api.unauthorized');

        return $this->error($message, 'UNAUTHORIZED', [], 401);
    }

    /**
     * Доступ запрещён (HTTP 403).
     *
     * @param string|null $message Кастомное сообщение
     *
     * @return JsonResponse
     */
    public function forbidden(?string $message = null): JsonResponse
    {
        $message = $message ?? __('api.forbidden');

        return $this->error($message, 'FORBIDDEN', [], 403);
    }

    /**
     * Отправка ответа. Автоматически обрабатывает пагинацию.
     *
     * @return JsonResponse
     */
    private function send(): JsonResponse
    {
        if ($this->success) {
            $response = ['success' => true];

            if ($this->data !== null) {
                if ($this->data instanceof LengthAwarePaginator) {
                    $response['data'] = [
                        'items' => $this->data->items(),
                        'meta'  => [
                            'pagination' => [
                                'current_page' => $this->data->currentPage(),
                                'per_page'     => $this->data->perPage(),
                                'total'        => $this->data->total(),
                                'last_page'    => $this->data->lastPage(),
                                'from'         => $this->data->firstItem(),
                                'to'           => $this->data->lastItem(),
                            ],
                        ],
                    ];
                } elseif (
                    $this->data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection
                    && $this->data->resource instanceof LengthAwarePaginator
                ) {
                    /** @var LengthAwarePaginator $paginator */
                    $paginator = $this->data->resource;

                    $response['data'] = [
                        'items' => $this->data,
                        'meta'  => [
                            'pagination' => [
                                'current_page' => $paginator->currentPage(),
                                'per_page'     => $paginator->perPage(),
                                'total'        => $paginator->total(),
                                'last_page'    => $paginator->lastPage(),
                                'from'         => $paginator->firstItem(),
                                'to'           => $paginator->lastItem(),
                            ],
                        ],
                    ];
                } else {
                    $response['data'] = $this->data;
                }
            }

            return response()->json($response, $this->statusCode);
        } else {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $this->errorCode,
                    'message' => $this->errorMessage,
                    'details' => $this->errorDetails,
                ],
            ], $this->statusCode);
        }
    }

    /**
     * Статический метод ошибки.
     *
     * @param string              $message Сообщение
     * @param string              $code    Код ошибки
     * @param array<string,mixed> $details Детали
     * @param int                 $status  HTTP-код
     *
     * @return JsonResponse
     */
    public static function errorStatic(
        string $message,
        string $code = 'ERROR',
        array $details = [],
        int $status = 400
    ): JsonResponse {
        return static::make()->error($message, $code, $details, $status);
    }

    /**
     * Статический метод — не авторизован.
     *
     * @param string|null $message Кастомное сообщение
     *
     * @return JsonResponse
     */
    public static function unauthorizedStatic(?string $message = null): JsonResponse
    {
        return static::make()->unauthorized($message);
    }

    /**
     * Статический метод — доступ запрещён.
     *
     * @param string|null $message Кастомное сообщение
     *
     * @return JsonResponse
     */
    public static function forbiddenStatic(?string $message = null): JsonResponse
    {
        return static::make()->forbidden($message);
    }

    /**
     * Статический метод — успешный ответ.
     *
     * @param mixed $data   Данные
     * @param int   $status HTTP-код
     *
     * @return JsonResponse
     */
    public static function successStatic(mixed $data = null, int $status = 200): JsonResponse
    {
        return static::make()->success($data, $status);
    }
}
