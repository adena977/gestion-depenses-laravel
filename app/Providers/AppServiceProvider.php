<?php

namespace App\Providers;
use App\Models\SavingsGoal;
use App\Observers\SavingsGoalObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
           Schema::defaultStringLength(191);
              // Enregistrer l'observateur pour SavingsGoal
        SavingsGoal::observe(SavingsGoalObserver::class);
    }
}
