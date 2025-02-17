<?php

namespace App\Observers;

use App\Models\DataChange;
use App\Models\Pricelist;

class PricelistObserver
{
	public function updated(Pricelist $pricelist): void
	{
		DataChange::storeChange($pricelist);
		$pricelist->flushCache();
	}

	public function stored(Pricelist $pricelist): void
	{
		$pricelist->flushCache();
	}
}
