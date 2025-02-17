<?php

namespace App\Filters\Reservation;

use App\Custom\Timezone;
use App\Filters\Filter;
use App\Models\Game;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StartAtRangeFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'startRange';

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		$startRange = request()?->get('filters')[$tableName][self::$filterKey];

		if (!str_contains($startRange['from'], ' ')) {
			$openingHours = club()->getOpeningHoursForDate($startRange['from']);
			$startRange['from'] = $startRange['from'] . ' ' . $openingHours['club_start'];
		}

		if (!str_contains($startRange['to'], ' ')) {
			$openingHours = club()->getOpeningHoursForDate($startRange['to']);
			$clubStart = now()->parse($openingHours['club_start']);
			$clubEnd = now()->parse($openingHours['club_end']);

			$startRange['to'] = $startRange['to'] . ' ' . $openingHours['club_end'];
			if ($clubEnd->lt($clubStart)) {
				$startRange['to'] = now()
					->parse($startRange['to'])
					->addDay()
					->format('Y-m-d H:i:s');
			}
		}

		return $query->whereHas('reservationSlots', function ($query) use ($startRange) {
			$query->whereBetween('start_at', [
				Timezone::convertFromLocal($startRange['from']),
				Timezone::convertFromLocal($startRange['to']),
			]);
		});
	}
}
