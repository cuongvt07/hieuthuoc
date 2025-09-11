<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Check for expiring medicines every day at midnight
            $schedule->call(function() {
                app(\App\Http\Controllers\ThongBaoController::class)->checkExpiredMedicines();
            })->everyMinute();
        });
    }
}
