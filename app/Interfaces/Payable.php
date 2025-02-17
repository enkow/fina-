<?php

namespace App\Interfaces;

use Carbon\CarbonInterface;

/**
 * @property CarbonInterface $paid_at
 */
interface Payable
{
	public function getPaymentCurrency(): string;

	public function getPaymentTotal(): int;

	public function getPaymentCommission(): int;
}
