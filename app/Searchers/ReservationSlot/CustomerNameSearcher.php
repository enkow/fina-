<?php

namespace App\Searchers\ReservationSlot;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class CustomerNameSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->whereHas('reservation', function ($query) use ($tableName) {
			$query->whereHas('customer', function ($query) use ($tableName) {
				$query->whereRaw(
					'CONCAT(customers.first_name, " ", customers.last_name) like "%' .
						request()?->get('searcher')[$tableName] .
						'%"'
				);
			});
		});
	}
}
