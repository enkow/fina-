<?php

namespace App\Filters\Reservation;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CancelationTypeFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'cancelationType';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		if (request()?->get('filters')[$tableName][self::$filterKey] === '0') {
			return $query;
		}

		return $query->whereHas('reservationSlots', function ($query) use ($tableName) {
			$query->whereIn(
				'cancelation_type',
				explode(',', request()?->get('filters')[$tableName][self::$filterKey])
			);
		});
	}
}
