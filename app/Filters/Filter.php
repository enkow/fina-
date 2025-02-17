<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
	//Added in the Filterable trait method, it allows you to filter the query
	public static function handle(Builder $query, string $tableName): Builder;
}
