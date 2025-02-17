<?php

namespace App\Providers;

use App\Channels\SmsChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
	/**
	 * All of the container singletons that should be registered.
	 *
	 * @var array
	 */
	public $singletons = [
		'sms' => SmsChannel::class,
	];

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		Notification::resolved(function (ChannelManager $service) {
			$service->extend('sms', function ($app) {
				return $app->make('sms');
			});
		});
	}
}
