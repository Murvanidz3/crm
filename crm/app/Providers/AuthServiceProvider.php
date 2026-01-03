<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\CarPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Car::class => CarPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register policies
        $this->registerPolicies();

        // Define gates for transaction operations
        Gate::define('create-transaction', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('update-transaction', function (User $user, Transaction $transaction) {
            return $user->isAdmin();
        });

        Gate::define('delete-transaction', function (User $user, Transaction $transaction) {
            return $user->isAdmin();
        });

        // Admin-only gate
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        // Finance access gate
        Gate::define('access-finance', function (User $user) {
            return $user->isAdmin() || $user->isDealer();
        });

        // Wallet access gate
        Gate::define('access-wallet', function (User $user) {
            return $user->isAdmin() || $user->isDealer();
        });

        // SMS management gate
        Gate::define('manage-sms', function (User $user) {
            return $user->isAdmin();
        });
    }
}
