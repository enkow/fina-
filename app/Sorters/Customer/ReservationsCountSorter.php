<?php

namespace App\Sorters\Customer;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ReservationsCountSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'reservations_count';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->orderByRaw('count(reservations.id) ' . request()?->get('sorters')[$tableName][self::$filterKey])
			->groupBy('reservations.customer_id');
	}
}
