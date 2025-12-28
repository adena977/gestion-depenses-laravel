<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\BudgetApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Transactions API
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionApiController::class, 'index']);
        Route::post('/', [TransactionApiController::class, 'store']);
        Route::get('/{id}', [TransactionApiController::class, 'show']);
        Route::put('/{id}', [TransactionApiController::class, 'update']);
        Route::delete('/{id}', [TransactionApiController::class, 'destroy']);
        Route::get('/stats/monthly', [TransactionApiController::class, 'monthlyStats']);
    });
    
    // Categories API
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryApiController::class, 'index']);
        Route::post('/', [CategoryApiController::class, 'store']);
        Route::get('/{id}', [CategoryApiController::class, 'show']);
        Route::put('/{id}', [CategoryApiController::class, 'update']);
        Route::delete('/{id}', [CategoryApiController::class, 'destroy']);
    });
    
    // Budgets API
    Route::prefix('budgets')->group(function () {
        Route::get('/', [BudgetApiController::class, 'index']);
        Route::post('/', [BudgetApiController::class, 'store']);
        Route::get('/{id}', [BudgetApiController::class, 'show']);
        Route::put('/{id}', [BudgetApiController::class, 'update']);
        Route::delete('/{id}', [BudgetApiController::class, 'destroy']);
        Route::get('/current/progress', [BudgetApiController::class, 'currentProgress']);
    });
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});