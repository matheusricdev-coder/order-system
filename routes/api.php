<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminStockController;
use Illuminate\Support\Facades\Route;

$apiV1 = static function (): void {
    // ── Public catalog ────────────────────────────────────────────────────
    Route::get('/products',                 [CatalogController::class, 'index']);
    Route::get('/products/{slug}',          [CatalogController::class, 'show']);
    Route::get('/categories',               [CatalogController::class, 'categories']);

    Route::get('/companies/{id}',           [CompanyController::class, 'show']);
    Route::get('/companies/{id}/products',  [CompanyController::class, 'products']);

    Route::get('/stocks/{productId}',       [StockController::class, 'show']);
    Route::get('/products/{id}/stock',      [StockController::class, 'showByProduct']);

    // ── Auth ──────────────────────────────────────────────────────────────
    Route::post('/auth/register',           [AuthController::class, 'register']);
    Route::post('/auth/login',              [AuthController::class, 'login']);
    Route::post('/auth/logout',             [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me',                       [AuthController::class, 'me'])->middleware('auth:sanctum');
    Route::get('/me/login-streak',          [AuthController::class, 'loginStreak'])->middleware('auth:sanctum');

    // ── Orders (all require authentication) ───────────────────────────────
    Route::middleware('auth:sanctum')->group(static function (): void {
        Route::post('/orders',                              [OrderController::class, 'create']);
        Route::post('/orders/{orderNumber}/pay',            [OrderController::class, 'pay']);
        Route::post('/orders/{orderNumber}/cancel',         [OrderController::class, 'cancel']);
        Route::get('/orders/{orderNumber}',                 [OrderController::class, 'show']);
        Route::get('/orders',                               [OrderController::class, 'index']);
    });
};

Route::prefix('v1')->middleware('throttle:api')->group($apiV1);

// Backward-compatibility alias: routes without /v1 prefix are deprecated.
// Planned removal: once all consumers have migrated to /api/v1.
// @deprecated Use /api/v1/* instead.
Route::middleware('throttle:api')->group($apiV1);

// ── Admin (requires authentication + admin role) ───────────────────────────
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'admin', 'throttle:api'])
    ->group(static function (): void {
        // Products CRUD
        Route::get('products',              [AdminProductController::class, 'index']);
        Route::post('products',             [AdminProductController::class, 'store']);
        Route::get('products/{id}',         [AdminProductController::class, 'show']);
        Route::put('products/{id}',         [AdminProductController::class, 'update']);
        Route::delete('products/{id}',      [AdminProductController::class, 'destroy']);

        // Stock management
        Route::get('stocks',                [AdminStockController::class, 'index']);
        Route::put('stocks/{productId}',    [AdminStockController::class, 'update']);

        // Orders (read-only view of all orders)
        Route::get('orders',                [AdminOrderController::class, 'index']);
        Route::get('orders/{id}',           [AdminOrderController::class, 'show']);

        // Ping / health
        Route::get('ping', static fn () => response()->json(['status' => 'admin-ok']));
    });

// ── Stripe webhook — no auth, protected by signature verification ─────────────
// Must be registered BEFORE the throttle groups to avoid rate-limit interference.
Route::post('/webhooks/stripe', [WebhookController::class, 'handle']);

// Health check — used by Railway and uptime monitors.
Route::get('/health', static fn () => response()->json([
    'status'    => 'ok',
    'timestamp' => now()->toISOString(),
]));
