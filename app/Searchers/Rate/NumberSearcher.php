<?php

namespace App\Searchers\Rate;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class NumberSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->whereHas('reservationSlots', function ($query) use ($tableName) {
				$query->whereHas('reservationNumber', function ($query) use ($tableName) {
					$query->whereRaw(
						'LPAD(reservation_numbers.id, 5, "0") LIKE "%' .
							request()->get('searcher')[$tableName] .
							'%"'
					);
				});
			})
			->orWhereHas('reservationNumber', function ($query) use ($tableName) {
				$query->whereRaw(
					'LPAD(reservation_numbers.id, 5, "0") LIKE "%' .
						request()->get('searcher')[$tableName] .
						'%"'
				);
			});
	}
}
