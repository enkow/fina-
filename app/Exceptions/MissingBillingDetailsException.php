<?php

namespace App\Exceptions;

use Exception;

class MissingBillingDetailsException extends Exception
{
	public function __construct()
	{
		parent::__construct('The club did not provide billing details (stripe customer does not exist).');
	}
}
