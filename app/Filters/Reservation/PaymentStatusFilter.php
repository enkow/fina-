<?php

namespace App\Filters\Reservation;

use App\Enums\ReservationSlotStatus;
use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentStatusFilter implements Filter
{
	public static bool $inRouteAttribute = false;
	public static string $filterKey = 'paymentStatus';

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public static function handle(Builder $query, string $tableName): Builder
	{
		if (request()?->get('filters')[$tableName][self::$filterKey] === '0') {
			return $query;
		}

		return $query->where(function ($query) use ($tableName) {
			$query->whereNull('id');
			foreach (
				explode(',', request()?->get('filters')[$tableName][self::$filterKey])
				as $paymentStatusFilter
			) {
				if ($paymentStatusFilter === '1') {
					$query->orWhere(function ($query) {
						$query->where('status', ReservationSlotStatus::Pending);
						$query->where('created_at', '<', now()->subMinutes(5));
					});
				}
				if ($paymentStatusFilter === '2') {
					$query->orWhere('status', ReservationSlotStatus::Confirmed);
				}
				if ($paymentStatusFilter === '3') {
					$query->orWhereHas('club_reservation', true);
				}
				if ($paymentStatusFilter === '4') {
					$query->orWhere(function ($query) {
						$query->where('status', ReservationSlotStatus::Pending);
						$query->where('created_at', '>', now()->subMinutes(5));
					});
				}
			}
		});
	}
}
