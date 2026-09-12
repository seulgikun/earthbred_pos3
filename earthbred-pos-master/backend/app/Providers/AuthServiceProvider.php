<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // RBAC Gates
        Gate::define('manage-users', function (User $user) {
            return $user->role === 'owner';
        });

        Gate::define('access-manager', function (User $user) {
            return in_array($user->role, ['owner', 'manager']);
        });

        Gate::define('update-void-pin', function (User $user) {
            return $user->role === 'owner';
        });

        Gate::define('manage-inventory', function (User $user) {
            return in_array($user->role, ['owner', 'manager']);
        });
    }
}
