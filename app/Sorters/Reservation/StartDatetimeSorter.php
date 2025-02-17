<?php

namespace App\Sorters\Reservation;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StartDatetimeSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'start_datetime';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->join('reservation_slots', 'reservations.id', '=', 'reservation_slots.reservation_id')
			->orderByRaw(
				'min(reservation_slots.start_at) ' . request()?->get('sorters')[$tableName][self::$filterKey]
			)
			->groupBy('reservation_slots.reservation_id');
	}
}
