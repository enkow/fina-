<?php

namespace App\Searchers\Reservation;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class NumberSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query
			->with('reservationSlots', 'reservationNumber')
			->whereHas('reservationSlots', function ($query) {
				$query->where('created_at', function ($query) {
					$query
						->selectRaw('MIN(created_at)')
						->from('reservation_slots')
						->whereColumn('reservation_id', 'reservations.id');
				});
			})
			->whereHas('reservationNumber', function ($query) use ($tableName) {
				$query->whereRaw(
					'CONCAT(DATE_FORMAT((SELECT MIN(created_at) FROM reservation_slots WHERE reservation_id = reservations.id), "%y%m%d"), "-", LPAD(reservation_numbers.id, 5, "0")) LIKE ?',
					['%' . request()->get('searcher')[$tableName] . '%']
				);
			});
	}
}
