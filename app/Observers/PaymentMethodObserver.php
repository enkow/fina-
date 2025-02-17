<?php

namespace App\Observers;

use App\Models\Club;
use App\Models\PaymentMethod;

class PaymentMethodObserver
{
	public function stored(PaymentMethod $paymentMethod): void
	{
		$paymentMethod->flushCache();
		if ($paymentMethod->club_id) {
			Club::find($paymentMethod->club_id)->flushCache();
		}
	}

	public function updated(PaymentMethod $paymentMethod): void
	{
		info('before_payment_method_if');
		$paymentMethod->flushCache();
		info('before_if');
		info($paymentMethod->club_id);
		if ($paymentMethod->club_id) {
			info('after_if');
			Club::find($paymentMethod->club_id)->flushCache();
		}
	}

	public function deleted(PaymentMethod $paymentMethod): void
	{
		$paymentMethod->flushCache();
		if ($paymentMethod->club_id) {
			Club::find($paymentMethod->club_id)->flushCache();
		}
	}
}
