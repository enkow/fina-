<?php

namespace App\Traits;

use App\Filters\Filter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait Filterable
{
	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function scopeFilterable($query, $tableName, $filters)
	{
		foreach ($filters as $filter) {
			if (
				!is_subclass_of($filter, Filter::class) ||
				($filter::$inRouteAttribute && !request()?->route($filter::$filterKey)) ||
				($filter::$inRouteAttribute === false &&
					(!request()?->has('filters') ||
						!isset(request()?->get('filters')[$tableName][$filter::$filterKey]))) ||
				($filter::$inRouteAttribute === null &&
					(!request()?->route($filter::$filterKey) &&
						(!request()?->has('filters') ||
							!isset(request()?->get('filters')[$tableName][$filter::$filterKey]))))
			) {
				continue;
			}
			$query = $filter::handle($query, $tableName);
		}

		return $query;
	}
}
