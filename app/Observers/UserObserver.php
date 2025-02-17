<?php

namespace App\Observers;

use App\Mail\RegisterInvitation;
use App\Models\User;
use App\Notifications\RegisterInvitationNotification;
use Illuminate\Support\Facades\App;

class UserObserver
{
	/**
	 * Handle the User "created" event.
	 *
	 * @param  User  $user
	 *
	 * @return void
	 */
	public function created(User $user): void
	{
		if (!App::isLocal()) {
			$user->notify(new RegisterInvitationNotification());
		}
	}

	/**
	 * Handle the User "deleted" event.
	 *
	 * @param  User  $user
	 *
	 * @return void
	 */
	public function deleted(User $user): void
	{
		$user->email .= '_' . now() . '_deleted';
		$user->save();
	}
}
