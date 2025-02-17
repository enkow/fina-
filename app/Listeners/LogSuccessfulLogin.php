<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  object  $event
	 *
	 * @return void
	 */
	public function handle(Login $event)
	{
		$event->user->last_login = now();
		$event->user->save();
	}
}
