<?php

namespace App\Searchers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CustomerNameSearcher implements Searcher
{
	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		return $query->where(
			DB::raw("CONCAT(`first_name`, ' ', `last_name`)"),
			'LIKE',
			'%' . request()?->get('searcher')[$tableName] . '%'
		);
	}
}
