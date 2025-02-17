<?php

namespace App\Sorters\ReservationSlot;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;

class StatusSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'status_locale';

	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->orderByRaw(
				'IF(cancelation_type IS NULL, 0, 1) ' .
					request()?->get('sorters')[$tableName][self::$filterKey]
			)
			->orderBy(
				'reservation_slots.cancelation_type',
				request()?->get('sorters')[$tableName][self::$filterKey]
			)
			->orderBy('reservation_slots.status', request()?->get('sorters')[$tableName][self::$filterKey]);
	}
}
