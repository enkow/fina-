<?php

namespace App\Sorters\ReservationSlot;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
		return $query
			->withCount([
				'sets as pivot_price_sum' => function ($query) {
					$query->select(DB::raw('SUM(reservation_slot_set.price)'));
				},
			])
			->orderBy(
				DB::raw('(
                COALESCE(`final_price`, 0) + 
                COALESCE(`club_commission_partial`, 0) + 
                COALESCE(`pivot_price_sum`, 0)
            )'),
				request()?->get('sorters')[$tableName][self::$filterKey]
			);
	}
}
