<?php

namespace App\Traits;

use App\Sorters\Sorter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait Sortable
{
	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public function scopeSortable($query, $tableName, $sorters)
	{
		foreach ($sorters as $sorter) {
			if (is_subclass_of($sorter, Sorter::class)) {
				if (
					($sorter::$inRouteAttribute && !request()?->route($sorter::$filterKey)) ||
					(!$sorter::$inRouteAttribute &&
						(!request()?->has('sorters') ||
							!isset(request()?->get('sorters')[$tableName][$sorter::$filterKey])))
				) {
					continue;
				}
				$query = $sorter::handle($query, $tableName);
			} elseif (request()?->get('sorters')[$tableName][$sorter] ?? null) {
				$query = $query->orderBy($sorter, request()?->get('sorters')[$tableName][$sorter]);
			}
		}

		return $query;
	}
}
