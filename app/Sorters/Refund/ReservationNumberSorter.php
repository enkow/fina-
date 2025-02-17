<?php

namespace App\Sorters\Refund;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ReservationNumberSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'refundable_id';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderBy(
			'reservation_number_sort',
			request()?->get('sorters')[$tableName][self::$filterKey]
		);
	}
}
