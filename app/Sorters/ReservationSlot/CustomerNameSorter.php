<?php

namespace App\Sorters\ReservationSlot;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CustomerNameSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'customer_name';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
			->leftJoin('customers', 'reservations.customer_id', '=', 'customers.id')
			->orderByRaw(
				'CONCAT(customers.first_name, customers.last_name) ' .
					request()?->get('sorters')[$tableName][self::$filterKey]
			)
			->select('reservation_slots.*');
	}
}
