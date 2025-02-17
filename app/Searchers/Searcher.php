<?php

namespace App\Searchers;

use Illuminate\Database\Eloquent\Builder;

interface Searcher
{
	//Added in the Searchable trait method, it allows you to search the query
	public static function handle(Builder $query, string $tableName): Builder;
}
