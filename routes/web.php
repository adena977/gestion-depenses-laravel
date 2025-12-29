<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    // Profile (déjà géré par Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Transactions - Routes CRUD complètes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
        
        // Routes supplémentaires
        Route::get('/export/excel', [TransactionController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [TransactionController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/import', [TransactionController::class, 'import'])->name('import');
    });
    
    // Catégories - Routes CRUD
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        
        // Routes API pour sélection
        Route::get('/api/user-categories', [CategoryController::class, 'getUserCategories'])->name('api.user-categories');
    });
    
    // Budgets - Routes CRUD
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::get('/create', [BudgetController::class, 'create'])->name('create');
        Route::post('/', [BudgetController::class, 'store'])->name('store');
        Route::get('/{budget}', [BudgetController::class, 'show'])->name('show');
        Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('edit');
        Route::put('/{budget}', [BudgetController::class, 'update'])->name('update');
        Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
        
        // Routes spécifiques
        Route::get('/current-month', [BudgetController::class, 'currentMonth'])->name('current-month');
        Route::get('/{budget}/progress', [BudgetController::class, 'progress'])->name('progress');
    });
    
    // Rapports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        Route::get('/yearly', [ReportController::class, 'yearly'])->name('yearly');
        Route::get('/category', [ReportController::class, 'byCategory'])->name('category');
        Route::get('/trends', [ReportController::class, 'trends'])->name('trends');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
    
Route::prefix('savings')->name('savings.')->group(function () {
        Route::get('/', [SavingsGoalController::class, 'index'])->name('index');
        Route::get('/create', [SavingsGoalController::class, 'create'])->name('create');
        Route::post('/', [SavingsGoalController::class, 'store'])->name('store');
        Route::get('/{savingsGoal}', [SavingsGoalController::class, 'show'])->name('show');
        Route::get('/{savingsGoal}/edit', [SavingsGoalController::class, 'edit'])->name('edit');
        Route::put('/{savingsGoal}', [SavingsGoalController::class, 'update'])->name('update');
        Route::delete('/{savingsGoal}', [SavingsGoalController::class, 'destroy'])->name('destroy');
        
        // Actions simples
        Route::post('/{savingsGoal}/add-funds', [SavingsGoalController::class, 'addFunds'])->name('add-funds');
        Route::post('/{savingsGoal}/withdraw-funds', [SavingsGoalController::class, 'withdrawFunds'])->name('withdraw-funds');
        Route::post('/{savingsGoal}/complete', [SavingsGoalController::class, 'complete'])->name('complete');
        Route::post('/{savingsGoal}/reactivate', [SavingsGoalController::class, 'reactivate'])->name('reactivate');
    });
});
    // API pour dashboard (données AJAX)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard-stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/monthly-expenses', [DashboardController::class, 'monthlyExpenses'])->name('dashboard.monthly-expenses');
        Route::get('/category-breakdown', [DashboardController::class, 'categoryBreakdown'])->name('dashboard.category-breakdown');
        Route::get('/recent-transactions', [TransactionController::class, 'recent'])->name('transactions.recent');
        Route::get('/budget-alerts', [BudgetController::class, 'alerts'])->name('budgets.alerts');
    });
    
    



// Routes d'authentification Breeze (déjà incluses)
require __DIR__.'/auth.php';