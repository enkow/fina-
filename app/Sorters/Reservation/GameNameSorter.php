<?php

namespace App\Sorters\Reservation;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GameNameSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'game_name';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderBy('game_id', request()?->get('sorters')[$tableName][self::$filterKey]);
	}
}
