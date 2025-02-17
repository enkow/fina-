<?php

namespace App\Sorters\ReservationSlot;

use App\Sorters\Sorter;
use Illuminate\Database\Eloquent\Builder;

class SlotsCountSorter implements Sorter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'slots_count';

	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query;
	}
}
