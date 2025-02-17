<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		JsonResource::withoutWrapping();
		URL::forceRootUrl(Config::get('app.url'));
		if (str_contains(Config::get('app.url'), 'https://')) {
			URL::forceScheme('https');
		}

		Stripe::setEnableTelemetry(false);
		Stripe::setApiKey(config('services.stripe.secret'));
	}
}
