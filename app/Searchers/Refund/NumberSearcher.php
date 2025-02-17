<?php

namespace App\Searchers\Refund;

use App\Searchers\Searcher;
use Illuminate\Database\Eloquent\Builder;

class NumberSearcher implements Searcher
{
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->whereRaw('reservation_numbers_search LIKE ?', [
			'%' . request()->get('searcher')[$tableName] . '%',
		]);
	}
}
