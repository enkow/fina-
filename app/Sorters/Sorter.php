<?php

namespace App\Sorters;

use Illuminate\Database\Eloquent\Builder;

interface Sorter
{
	//Added in the Sortable trait method, it allows you to sort the query
	public static function handle(Builder $query, string $tableName);
}
