<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class RegisterInvitationNotification extends Notification implements ShouldQueue
{
	use Queueable;

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return array
	 */
	public function via(User $notifiable): array
	{
		return ['mail'];
	}

	/**
	 * Send user register invitation
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return MailMessage
	 */
	public function toMail(User $recipient): MailMessage|bool
	{
		if (!$recipient->club) {
			return false;
		}

		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('employee.new-club-account'))
			->markdown('vendor.notifications.email', [
				'level' => 'primary',
				'markdown' => false,
				'actionText' => __('employee.set-password'),
				'actionUrl' => route('showRegisterForm', ['secret' => Crypt::encrypt($recipient->id)]),
				'introLines' => [
					"<br><p style='font-size:15px;margin-bottom:0;display:block;width:100%;'>" .
					__('employee.new-club-account-created', ['club_name' => $recipient->club->name]) .
					'<br>' .
					__('employee.click-to-set-password') .
					'</p>',
				],
			]);
	}
}
