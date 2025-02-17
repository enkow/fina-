<?php

namespace App\Exceptions;

use Exception;

class MissingPaymentMethodException extends Exception
{
	public function __construct()
	{
		parent::__construct('The club did not add or select a default payment method.');
	}
}
