<?php

namespace App\Observers;

use App\Models\Refund;

class RefundObserver
{
	public function updated(Refund $refund): void
	{
		$refund->refreshHelperColumns();
	}
}
