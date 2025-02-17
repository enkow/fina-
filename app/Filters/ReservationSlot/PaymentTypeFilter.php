<?php

namespace App\Filters\ReservationSlot;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentTypeFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'paymentType';

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		if (request()?->get('filters')[$tableName][self::$filterKey] === '0') {
			return $query;
		}

		return $query->whereHas('reservation', function ($query) use ($tableName) {
			$query->whereHas('paymentMethod', function ($query) use ($tableName) {
				$query->where(
					'online',
					match (request()?->get('filters')[$tableName][self::$filterKey]) {
						'2' => true,
						default => false,
					}
				);
			});
		});
	}
}
