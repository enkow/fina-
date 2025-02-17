<?php

namespace App\Enums;

enum SmsProvider: int
{
	case JUSTSEND = 0;
	case SMSAPI = 1;

	static function fromString($type)
	{
		return [
			'justsend' => SmsProvider::JUSTSEND,
			'smsapi' => SmsProvider::SMSAPI,
		][$type] ?? null;
	}
}
