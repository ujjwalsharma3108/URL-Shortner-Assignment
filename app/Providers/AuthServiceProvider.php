<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
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
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function (User $user) {
            return in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin], true);
        });

        Gate::define('manage-admins', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('create-short-url', function (User $user) {
            return ! $user->isSuperAdmin();
        });

        Gate::define('view-super-admin-analytics', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('view-admin-analytics', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-team', function (User $user) {
            return $user->isAdmin();
        });
    }
}
