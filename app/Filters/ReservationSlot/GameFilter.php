<?php

namespace App\Filters\ReservationSlot;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GameFilter implements Filter
{
	public static bool|null $inRouteAttribute = null;
	public static string $filterKey = 'game';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		$gameId = match (true) {
			isset(request()?->get('filters')[$tableName][self::$filterKey]) => request()?->get('filters')[
				$tableName
			][self::$filterKey],
			request()->route('game') !== null => request()->route('game')->id,
			default => 0,
		};

		return $query->whereHas('reservation', function ($query) use ($gameId) {
			$query->where('game_id', $gameId);
		});
	}
}
