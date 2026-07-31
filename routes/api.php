<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Clients\Http\Controllers\ClientController;
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

    // Clients
    Route::apiResource('clients', ClientController::class);

    // Memberships - Types
    Route::patch('/membership-types/{id}/toggle', [\App\Modules\Memberships\Http\Controllers\MembershipTypeController::class, 'toggle']);
    Route::apiResource('membership-types', \App\Modules\Memberships\Http\Controllers\MembershipTypeController::class);

    // Memberships - Purchases
    Route::apiResource('membership-purchases', \App\Modules\Memberships\Http\Controllers\MembershipPurchaseController::class)->only(['index', 'show', 'store']);

    // Visits — будет добавлено
    // Reports — будет добавлено
});
