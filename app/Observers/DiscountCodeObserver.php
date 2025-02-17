<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\DiscountCode;

class DiscountCodeObserver
{
	public function created(DiscountCode $discountCode): void
	{
		$discountCode->update([
			'creator_id' => auth()->user()?->id,
		]);
		club()?->flushCache();
	}

	public function updated(DiscountCode $discountCode): void
	{
		DataChange::storeChange($discountCode);
		club()?->flushCache();
	}

	public function deleted(DiscountCode $discountCode): void
	{
		club()?->flushCache();
	}
}
