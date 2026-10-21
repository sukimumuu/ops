<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Roles that must never receive the public property assistant.
     *
     * @var list<string>
     */
    private const CHATBOT_RESTRICTED_ROLES = ['Superadmin', 'Admin', 'PPAT'];

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
        Gate::define('use-chatbot', function (?User $user): bool {
            if ($user === null) {
                return true;
            }

            return ! $user->hasAnyRole(self::CHATBOT_RESTRICTED_ROLES);
        });

        Gate::define('fill-property-form', function (?User $user): bool {
            return $user !== null && ! $user->hasAnyRole(self::CHATBOT_RESTRICTED_ROLES);
        });
    }
}
