<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\PricelistItem;

class PricelistItemObserver
{
	public function updated(PricelistItem $pricelistItem): void
	{
		DataChange::storeChange($pricelistItem);
		$pricelistItem->pricelist->flushCache();
	}
	public function created(PricelistItem $pricelistItem): void
	{
		$pricelistItem->pricelist->flushCache();
	}
}
