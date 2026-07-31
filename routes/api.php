<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Все маршруты имеют префикс /api (настроено в bootstrap/app.php).
| Защищённые эндпоинты обёрнуты в middleware auth:sanctum.
|
*/

// --- Auth (публичные) ---
Route::post('/login', [AuthController::class, 'login']);

// --- Защищённые маршруты ---
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Clients — будет добавлено в следующем модуле
    // Memberships — будет добавлено
    // Visits — будет добавлено
    // Reports — будет добавлено
});
