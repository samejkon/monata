<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Admin;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        // Super Admin Rule
        Gate::before(function (Admin $admin, $ability) {
            return $admin->role === 'superadmin';
        });

        // Staff Permissions
        Gate::define('manage-booking', function (Admin $admin) {
            return $admin->role === 'staff';
        });
    }
}
