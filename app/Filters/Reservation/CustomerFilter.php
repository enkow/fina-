<?php

namespace App\Filters\Reservation;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CustomerFilter implements Filter
{
	public static bool $inRouteAttribute = true;
	public static string $filterKey = 'customer';

	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->where('customer_id', request()?->route(self::$filterKey)->id);
	}
}
