<?php

namespace App\Sorters\Reservation;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FinalPriceSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'final_price';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderBy('price', request()?->get('sorters')[$tableName][self::$filterKey]);
	}
}
