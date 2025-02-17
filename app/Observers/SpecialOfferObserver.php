<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\SpecialOffer;

class SpecialOfferObserver
{
	public function created(SpecialOffer $specialOffer): void
	{
		$specialOffer->update([
			'creator_id' => auth()->user()?->id,
		]);
		club()?->flushCache();
	}

	public function updated(SpecialOffer $specialOffer): void
	{
		DataChange::storeChange($specialOffer);
		club()?->flushCache();
	}
}
