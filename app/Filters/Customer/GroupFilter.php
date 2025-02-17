<?php

namespace App\Filters\Customer;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GroupFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'group';

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		if (request()?->get('filters')[$tableName][self::$filterKey] === '0') {
			return $query;
		}
		return $query->filter(request()?->get('filters')[$tableName][self::$filterKey]);
	}
}
