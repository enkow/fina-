<?php

namespace App\Sorters\Customer;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class NameSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'full_name';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->orderByRaw(
			'CONCAT(first_name, COALESCE(last_name, "")) ' .
				request()?->get('sorters')[$tableName][self::$filterKey]
		);
	}
}
