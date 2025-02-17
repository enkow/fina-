<?php

namespace App\Sorters\Reservation;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreatedDatetimeSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'created_datetime';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderBy('created_at', request()?->get('sorters')[$tableName][self::$filterKey]);
	}
}
