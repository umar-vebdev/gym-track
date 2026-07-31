<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Resources\UserResource;
use App\Modules\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @tags Аутентификация
 */
class AuthController extends Controller
{
    /**
     * Вход в систему
     *
     * Аутентификация владельца зала по email и паролю.
     * Возвращает Sanctum-токен для последующих запросов.
     * Старые токены для того же устройства удаляются автоматически.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "token": "1|abc123def456...",
     *     "user": {
     *       "id": 1,
     *       "name": "Owner",
     *       "email": "owner@gymtrack.local"
     *     }
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "error": {
     *     "code": "UNAUTHORIZED",
     *     "message": "Неверный логин или пароль",
     *     "details": []
     *   }
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return ApiResponse::make()->unauthorized('Неверный логин или пароль');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Удаляем старые токены для этого устройства (если передан device_name)
        $deviceName = $request->input('device_name', 'default');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return ApiResponse::make()->success([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    /**
     * Выход из системы
     *
     * Инвалидирует текущий Sanctum-токен.
     * После вызова токен больше не будет приниматься для аутентификации.
     *
     * @response 200 {
     *   "success": true
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "error": {
     *     "code": "UNAUTHORIZED",
     *     "message": "Необходима авторизация",
     *     "details": []
     *   }
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::make()->success();
    }
}
