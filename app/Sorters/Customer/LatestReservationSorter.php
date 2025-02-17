<?php

namespace App\Sorters\Customer;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LatestReservationSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'latest_reservation';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderByRaw(
			'reservations_max_created_at ' . request()?->get('sorters')[$tableName][self::$filterKey]
		);
	}
}
