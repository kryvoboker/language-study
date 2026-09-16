<?php

namespace httpdocs\backend\app\Providers;

use httpdocs\backend\app\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use function App\Providers\env;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->useStoragePath((string) env('APP_STORAGE_PATH', dirname(base_path(), 2).'/storage/backend'));
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(CarbonInterval::minutes(20));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));

        Gate::before(static function (User $user): ?bool {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}