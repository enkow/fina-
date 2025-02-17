<?php

namespace App\Sorters\ReservationSlot;

use App\Models\Feature;
use App\Models\ReservationSlot;
use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PersonCountSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'person_count';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->join('feature_payload', 'reservation_slots.id', '=', 'feature_payload.describable_id')
			->where('feature_payload.describable_type', (new ReservationSlot())->getMorphClass())
			->orderBy(
				DB::raw(
					'CAST(JSON_UNQUOTE(JSON_EXTRACT(feature_payload.data, "$.person_count")) AS UNSIGNED)'
				),
				request()?->get('sorters')[$tableName][self::$filterKey]
			)
			->groupBy('id')
			->select('reservation_slots.*');
	}
}
