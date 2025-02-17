<?php

namespace App\Traits;

use App\Searchers\Searcher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait Searchable
{
	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function scopeSearchable($query, $tableName, $searchers)
	{
		if (
			!count($searchers) ||
			!request()?->has('searcher') ||
			!isset(request()?->get('searcher')[$tableName])
		) {
			return $query;
		}

		return $query->where(function ($query) use ($tableName, $searchers) {
			$query->whereRaw('1 = 2');
			foreach ($searchers as $searcher) {
				if (is_subclass_of($searcher, Searcher::class)) {
					$query = $query->orWhere(function ($query) use ($searcher, $tableName) {
						$searcher::handle($query, $tableName);
					});
				} else {
					$query = $query->orWhere(
						$searcher,
						'like',
						'%' . request()?->get('searcher')[$tableName] . '%'
					);
				}
			}
		});
	}
}
