<?php

namespace App\Filters\ReservationSlot;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CancelationTypeFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'cancelationType';

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		if (request()?->get('filters')[$tableName][self::$filterKey] === '0') {
			return $query;
		}

		return $query->whereIn(
			'cancelation_type',
			explode(',', request()?->get('filters')[$tableName][self::$filterKey])
		);
	}
}
