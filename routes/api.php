<?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\CategoryController;
    use App\Http\Controllers\ProductController;
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\AnalyticsController;

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    //Buyers Can As well View These
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        // Buyer orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);

        //  Admin-only CRUD
        Route::middleware('admin')->group(function () {
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            Route::post('/products', [ProductController::class, 'store']);
        //     Route::put('/products/{id}', [ProductController::class, 'update']);
        //     Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        });
    });

    // Admin Order Management
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'allOrders']);
        Route::get('/admin/orders/{id}', [OrderController::class, 'viewOrder']);
        Route::put('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);

        Route::get('/admin/categories', [CategoryController::class, 'index']);
        Route::post('/admin/categories', [CategoryController::class, 'store']);
        Route::put('/admin/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);

        Route::get('/admin/analytics/orders/week', [AnalyticsController::class, 'ordersPerWeek']);
        Route::get('/admin/analytics/orders/month', [AnalyticsController::class, 'ordersPerMonth']);
        Route::get('/admin/analytics/orders/year', [AnalyticsController::class, 'ordersPerYear']);
        Route::get('/admin/analytics/orders/status', [AnalyticsController::class, 'ordersByStatus']);
        Route::get('/admin/analytics/users/month', [AnalyticsController::class, 'usersPerMonth']);
        Route::get('/admin/analytics/sales/total', [AnalyticsController::class, 'totalSales']);
        Route::get('/admin/analytics/summary', [AnalyticsController::class, 'summary']);
        Route::get('/admin/analytics/sales/week', [AnalyticsController::class, 'salesPerWeek']);
        Route::get('/admin/analytics/sales/month', [AnalyticsController::class, 'salesPerMonth']);
        Route::get('/admin/analytics/sales/year', [AnalyticsController::class, 'salesPerYear']);

    });
?>