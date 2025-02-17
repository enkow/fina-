<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\PricelistException;

class PricelistExceptionObserver
{
	public function updated(PricelistException $pricelistException): void
	{
		DataChange::storeChange($pricelistException);
		$pricelistException->pricelist->flushCache();
	}
	public function created(PricelistException $pricelistException): void
	{
		$pricelistException->pricelist->flushCache();
	}
}
