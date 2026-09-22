<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');

		Horizon::auth(function (Request $request) {
			$login = (string)config('horizon.auth_credentials.login', '');
			$pass  = (string)config('horizon.auth_credentials.password', '');
			$user  = $request->user();

			if (
				!($user instanceof User) ||
				$login === '' ||
				$pass === '' ||
				$request->getUser() !== $login ||
				$request->getPassword() !== $pass
			) {
				header('WWW-Authenticate: Basic realm="Horizon"');
				header('HTTP/1.0 401 Unauthorized');

				echo 'Authentication required.';

				exit();
			}

			return true;
		});
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user) {
			return $user instanceof User
				&& $user->is_active
				&& !$user->is_blocked
				&& !$user->hasRole('user')
				&& $user->can('access_admin_panel')
				&& in_array($user->email, config('horizon.allowed_emails', []), true);
        });
    }
}