<?php

namespace App\Sorters\ReservationSlot;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TimeRangeSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'reservation_time_range';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderByRaw(
			'time(start_at) ' . request()?->get('sorters')[$tableName][self::$filterKey]
		);
	}
}
