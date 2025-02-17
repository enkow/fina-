<?php

namespace App\Searchers\Reservation;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class CustomerPhoneSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->whereHas('customer', function ($query) use ($tableName) {
			$query->where('phone', 'like', '%' . request()?->get('searcher')[$tableName] . '%');
		});
	}
}
