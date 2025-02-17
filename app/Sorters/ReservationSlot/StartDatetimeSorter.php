<?php

namespace App\Sorters\ReservationSlot;

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
		return $query->orderBy('start_at', request()?->get('sorters')[$tableName][self::$filterKey]);
	}
}
