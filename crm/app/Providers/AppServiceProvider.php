<?php

namespace App\Providers;

use App\Services\CarFileService;
use App\Services\ClientService;
use App\Services\SmsService;
use App\Services\TransactionService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(TransactionService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(CarFileService::class);
        $this->app->singleton(ClientService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Custom Blade directives for role checking
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::if('dealer', function () {
            return auth()->check() && auth()->user()->isDealer();
        });

        Blade::if('client', function () {
            return auth()->check() && auth()->user()->isClient();
        });

        Blade::if('notclient', function () {
            return auth()->check() && !auth()->user()->isClient();
        });

        // Format currency directive
        Blade::directive('money', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });

        // Format currency without decimals
        Blade::directive('moneyShort', function ($amount) {
            return "<?php echo '$' . number_format($amount, 0); ?>";
        });
    }
}
