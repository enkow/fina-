<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * The path to the "home" route for your application.
	 *
	 * Typically, users are redirected here after authentication.
	 *
	 * @var string
	 */
	public const HOME = '/dashboard';

	/**
	 * Define your route model bindings, pattern filters, and other route configuration.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->configureRateLimiting();

		$this->routes(function () {
			Route::middleware('api')
				->prefix('api')
				->name('api.')
				->group(base_path('routes/api.php'));

			Route::middleware('web')->group(base_path('routes/web.php'));

			Route::middleware(['web', 'isUserType:admin'])
				->prefix('administrator')
				->name('admin.')
				->group(base_path('routes/admin.php'));

			Route::middleware(['web', 'isUserType:manager,employee', 'isPanelEnabled'])
				->prefix('club')
				->name('club.')
				->group(base_path('routes/club.php'));
		});
	}

	/**
	 * Configure the rate limiters for the application.
	 *
	 * @return void
	 */
	protected function configureRateLimiting()
	{
		RateLimiter::for('api', function (Request $request) {
			$token = $request->bearerToken();

			if ($token === config('app.sync_app_token')) {
				return Limit::perMinute(100000)->by($request->user()?->id ?: $request->ip());
			}

			return Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip());
		});
	}
}
