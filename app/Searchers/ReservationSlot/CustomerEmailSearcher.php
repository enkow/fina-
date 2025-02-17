<?php

namespace App\Searchers\ReservationSlot;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class CustomerEmailSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->whereHas('reservation', function ($query) use ($tableName) {
			$query->whereHas('customer', function ($query) use ($tableName) {
				$query->where('email', 'like', '%' . request()?->get('searcher')[$tableName] . '%');
			});
		});
	}
}
