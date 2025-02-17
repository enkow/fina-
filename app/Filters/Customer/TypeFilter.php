<?php

namespace App\Filters\Customer;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TypeFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'type';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return match (explode(',', request()?->get('filters')[$tableName][self::$filterKey])[0]) {
			'1' => $query->whereNotNull('password'),
			'2' => $query->whereNull('password'),
			default => $query,
		};
	}
}
