<?php

namespace App\Sorters\ReservationSlot;

use App\Models\ReservationSlot;
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
			->join('reservation_numbers', 'reservation_numbers.numerable_id', 'reservation_slots.id')
			->where('reservation_numbers.numerable_type', (new ReservationSlot())->getMorphClass())
			->orderBy('reservation_numbers.id', request()?->get('sorters')[$tableName][self::$filterKey])
			->select('reservation_slots.*');
	}
}
