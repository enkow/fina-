<?php

namespace App\Notifications\Customer;

use App\Models\Country;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordRecoveryNotification extends Notification implements ShouldQueue
{
	use Queueable;

	public function __construct(public Country $customCountry)
	{
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return array
	 */
	public function via(Customer $notifiable): array
	{
		return ['mail'];
	}

	public function toMail(Customer $notifiable): MailMessage
	{
		app()->setLocale($this->customCountry?->locale ?? $notifiable->locale);
		$token = md5($notifiable->recovery_password_token_base);

		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('widget.notifications.password-recovery.title'))
			->markdown('notifications::email', [
				'introLines' => __('widget.notifications.password-recovery.intro-lines'),
				'actionText' => __('widget.notifications.password-recovery.action-text'),
				'actionUrl' => route('widget.customers.password-recovery', [
					'club' => $notifiable->club,
					'customer' => $notifiable->id,
					'token' => $token,
				]),
				'level' => 'primary',
			]);
	}
}
