<?php

namespace App\Sorters\Reservation;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SlotsCountSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'slots_count';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderBy(
			'reservation_slots_count',
			request()?->get('sorters')[$tableName][self::$filterKey]
		);
	}
}
