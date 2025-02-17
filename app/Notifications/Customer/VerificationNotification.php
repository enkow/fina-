<?php

namespace App\Notifications\Customer;

use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\SmsCodeType;
use App\Enums\CustomerVerificationMethod;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class VerificationNotification extends Notification implements ShouldQueue
{
	use Queueable;

	public $reminders = [];
	public $customer;

	public function __construct(private readonly array $forceMethods = [])
	{
	}

	public function via(mixed $notifiable): array
	{
		$resultArray = [];
		$customer = $notifiable->load('reminders');
		$this->customer = $customer;
		$club = $customer->club;
		foreach ([ReminderMethod::Sms, ReminderMethod::Mail] as $reminderMethod) {
			$reminderMethodEntryExists = $customer->reminders
				->where('method', $reminderMethod)
				->where('type', ReminderType::RegisterCustomer)
				->first();

			$real = false;

			if (
				$reminderMethod === ReminderMethod::Sms &&
				$club->customer_verification_type === CustomerVerificationMethod::SMS
			) {
				$real = true;
			}
			if ($reminderMethod === ReminderMethod::Mail) {
				$real = true;
			}

			if (empty($reminderMethodEntryExists)) {
				$this->reminders[$reminderMethod->value] = $customer->reminders()->create([
					'method' => $reminderMethod,
					'type' => ReminderType::RegisterCustomer,
					'real' => $real,
				]);

				if ($real) {
					$resultArray[] = $reminderMethod->value;
				} else {
					$this->afterSms(['system' => 'SMS notifications off']);
				}
			} else {
				$this->reminders[$reminderMethod->value] = $reminderMethodEntryExists;
			}
		}

		return array_unique(array_merge($resultArray, $this->forceMethods));
	}

	public function toSms()
	{
		$customer = $this->customer;
		app()->setLocale($customer->locale);

		$code = $customer->smsCodes()->create([
			'type' => SmsCodeType::REGISTER,
		]);

		$data = [
			'code' => $code->code,
		];

		return __('reservation.sms.verification', $data, $customer->locale);
	}

	public function afterSms(mixed $result)
	{
		$this->reminders[ReminderMethod::Sms->value]->data = $result;
		$this->reminders[ReminderMethod::Sms->value]->save();
	}

	public function toMail(Customer $notifiable): MailMessage
	{
		app()->setLocale($notifiable->locale);
		return (new MailMessage())
			->subject(config('app.name') . ' - ' . __('widget.notifications.customer-verification.title'))
			->markdown('notifications::email', [
				'introLines' => __('widget.notifications.customer-verification.intro-lines'),
				'actionText' => __('widget.notifications.customer-verification.action-text'),
				'actionUrl' => route('widget.customers.email-verification', [
					'club' => $notifiable->club,
					'encryptedCustomerId' => Crypt::encrypt($notifiable->id),
				]),
				'level' => 'primary',
			]);
	}
}
