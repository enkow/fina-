<?php

namespace App\Searchers\ReservationSlot;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class NumberSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->whereHas('reservationNumber', function ($query) use ($tableName) {
			$query->whereRaw(
				'LPAD(reservation_numbers.id,5,"0") like "%' . request()->get('searcher')[$tableName] . '%"'
			);
		});
	}
}
