<?php

namespace App\Models;

use App\Custom\Timezone;
use App\Enums\RefundStatus;
use App\Sorters\Reservation\CreatedDatetimeSorter;
use App\Sorters\Reservation\CustomerNameSorter;
use App\Sorters\Reservation\NumberSorter;
use App\Sorters\Reservation\SlotsCountSorter;
use App\Sorters\Reservation\StartDatetimeSorter;
use App\Sorters\Reservation\TimeRangeSorter;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Refund extends BaseModel
{
	use Searchable, Sortable, HasRelationships;

	public static array $loggable = ['status', 'approver_id', 'approved_at', 'price'];
	protected $fillable = [
		'status',
		'approver_id',
		'approved_at',
		'price',
		'reservation_numbers_search',
		'reservation_number_sort',
	];
	public static array $availableSorters = ['id', 'created_at', 'price', 'reservation_number_sort'];

	protected $casts = [
		'approved_at' => 'datetime',
		'status' => RefundStatus::class,
	];

	public function approver(): HasOne
	{
		return $this->hasOne(User::class, 'id', 'approver_id');
	}

	public function reservationSlots(): HasMany
	{
		return $this->hasMany(ReservationSlot::class);
	}

	public function firstReservationSlot(): HasOne
	{
		return $this->hasOne(ReservationSlot::class)->orderBy('start_at');
	}

	public function reservation()
	{
		return $this->hasOneDeep(
			Reservation::class,
			[ReservationSlot::class],
			['id', 'id'],
			['id', 'reservation_id']
		);
	}

	public function reservationNumbers(): Builder
	{
		return ReservationNumber::select('reservation_numbers.*')
			->join('reservation_slots', 'reservation_numbers.numerable_id', '=', 'reservation_slots.id')
			->where('reservation_slots.refund_id', $this->id);
	}

	public function reservationNumber()
	{
		return $this->hasOneDeep(
			ReservationNumber::class,
			[ReservationSlot::class],
			['id', 'id'],
			['id', 'reservation_id']
		);
	}

	public function canBeApproved(User|int $user = null): bool
	{
		$reservation = $this->firstReservationSlot->reservation;
		$user = match (gettype($user)) {
			'integer' => User::findOrFail($user),
			'object' => $user,
			default => auth()->user(),
		};
		$reservationClubId = $reservation->firstReservationSlot->slot->pricelist->club_id;
		$refundTimeLimit = Setting::getClubGameSetting($reservationClubId, 'refund_time_limit')['value'];

		$paymentMethod = $reservation->paymentMethod;

		return match (true) {
			$this->created_at->subHours($refundTimeLimit)->gt($reservation->created_at) => false,
			$paymentMethod->club_id && !$user->isType('manager') => false,
			$paymentMethod->club_id === null && !$user->isType('admin') => false,
			$paymentMethod->club_id && $user->isType(['manager']) && $reservationClubId !== clubId() => false,
			default => true,
		};
	}

	public function approve(): array
	{
		$result = $this->firstReservationSlot->reservation
			->payments()
			->first()
			->refund($this->price);
		if ($this->status === RefundStatus::Pending) {
			$this->update([
				'status' => RefundStatus::Confirmed,
				'approver_id' => auth()->user()->id,
				'approved_at' => now(),
			]);
		}
		return $result;
	}

	public function refreshHelperColumns($reservationNumbersPreloaded = null)
	{
		$reservationNumbers =
			$reservationNumbersPreloaded ??
			($this->firstReservationSlot->reservation->game->hasFeature('person_as_slot')
				? [
					$this->firstReservationSlot->reservation
						->reservationNumber()
						->with('numerable')
						->select(
							'reservation_numbers.*',
							'reservation_slots.refund_id',
							DB::raw('CONCAT(customers.first_name, " ", customers.last_name) as customer_name')
						)
						->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
						->join('customers', 'reservations.customer_id', '=', 'customers.id')
						->get()
						->toArray(),
				]
				: $this->reservationNumbers()
					->with('numerable')
					->select(
						'reservation_numbers.*',
						'reservation_slots.refund_id',
						DB::raw('CONCAT(customers.first_name, " ", customers.last_name) as customer_name')
					)
					->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
					->join('customers', 'reservations.customer_id', '=', 'customers.id')
					->get()
					->toArray());

		if (count($reservationNumbers) === 0) {
			return;
		}

		$reservationNumbersId = [];
		foreach ($reservationNumbers as $reservationNumber) {
			$reservationNumbersId[] = $reservationNumber['id'];
		}

		$refund = $this;
		Refund::withoutEvents(function () use ($refund, $reservationNumbersId, $reservationNumbers) {
			$refund->update([
				'reservation_number_sort' =>
					count($reservationNumbersId) === 1
						? $reservationNumbersId[0]
						: (string) max($reservationNumbersId),
				'reservation_numbers_search' => $this->firstReservationSlot->reservation->customer->full_name.implode(',', $reservationNumbersId),
			]);
		});
	}

	/*
	 * Timezone handle
	 */

	protected function approvedAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}
}
