<?php

namespace App\Custom;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Timezone
{
	public static function getClubLocalizedDatetimeAttribute(): Attribute
	{
		return Attribute::make(
			get: static function (DateTime|string|null $value) {
				if (
					auth()->user() &&
					auth()
						->user()
						->isType(['manager', 'employee'])
				) {
					return Timezone::convertToLocal($value);
				}

				return is_string($value) ? now()->parse($value) : $value;
			},
			set: static function (DateTime|string|null $value) {
				if (
					auth()->user() &&
					auth()
						->user()
						->isType(['manager', 'employee'])
				) {
					return Timezone::convertFromLocal($value);
				}

				return is_string($value) ? now()->parse($value) : $value;
			}
		);
	}

	public static function convertToLocal(Carbon|string|null $datetime): null|bool|Carbon
	{
		return $datetime
			? Carbon::createFromFormat(
				'Y-m-d H:i:s',
				self::getCarbonObject($datetime)->format('Y-m-d H:i:s'),
				'UTC'
			)->timezone(date_default_timezone_get())
			: null;
	}

	public static function getCarbonObject(Carbon|string $datetime): Carbon
	{
		return is_string($datetime) ? now()->parse($datetime) : $datetime;
	}

	public static function convertFromLocal(Carbon|string|null $datetime): null|bool|Carbon
	{
		return $datetime ? self::getCarbonObject($datetime)->timezone('UTC') : null;
	}
}
