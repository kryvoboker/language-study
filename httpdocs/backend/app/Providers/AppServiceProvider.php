<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $new_storage_path = string_value(config('filesystems.new_storage_path'));

        if ($new_storage_path) {
            config([
                // Override compiled views path
                'view.compiled' => $new_storage_path . '/framework/views',
                'debugbar.storage.path' => $new_storage_path . '/debugbar',
                'logging.channels.single.path' => $new_storage_path . '/logs/laravel.log',
                'logging.channels.daily.path' => $new_storage_path . '/logs/laravel.log',
                'logging.channels.stack.path' => $new_storage_path . '/logs/laravel.log',
            ]);
        }

        $this->app->useStoragePath((string) env('NEW_STORAGE_PATH'));
    }

    public function boot(): void
    {
        require_once app_path('Supports/helpers.php');

        URL::forceRootUrl((string) config('app.url'));
        Passport::authorizationView('passport.authorize');

        Passport::tokensExpireIn(CarbonInterval::minutes(20));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::tokensCan([
            'profile' => 'Read the authenticated profile',
            'translate' => 'Use translation features',
        ]);
        Passport::setDefaultScope(['profile']);

        Gate::before(static function (User $user): ?bool {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
