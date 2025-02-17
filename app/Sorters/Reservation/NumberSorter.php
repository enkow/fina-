<?php

namespace App\Sorters\Reservation;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NumberSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'number';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->join('reservation_slots', 'reservations.id', '=', 'reservation_slots.reservation_id')
			->orderByRaw(
				'max(reservation_slots.id) ' . request()?->get('sorters')[$tableName][self::$filterKey]
			)
			->groupBy('reservation_slots.reservation_id');
	}
}
