<?php

namespace App\Models;

use App\Custom\Timezone;
use App\Enums\ReservationSlotCancelationType;
use App\Enums\ReservationSlotStatus;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JsonException;

class DataChange extends BaseModel
{
	protected $fillable = ['old', 'new', 'triggerer_id'];

	protected $casts = [
		'old' => 'array',
		'new' => 'array',
	];

	/**
	 * @throws JsonException
	 */
	public static function storeChange(Model $model): bool
	{
		$originalValues = $model->getOriginal();
		$loggableKeys = get_class($model)::$loggable ?? [];
		$changeResult = array_intersect_key($model->getChanges(), array_flip($loggableKeys));
		$oldData = $newData = [];

		foreach ($changeResult as $key => $value) {
			$oldData[$key] = $originalValues[$key] ?? null;
			$newData[$key] = $value;
			if (
				$oldData[$key] === $newData[$key] ||
				($oldData[$key] === 0 && $newData[$key] === false) ||
				($oldData[$key] === 1 && $newData[$key] === true)
			) {
				unset($oldData[$key], $newData[$key]);
			}
		}

		if (count($changeResult)) {
			try {
				if (count($oldData) + count($newData) > 0) {
					$model->changes()->create([
						'old' => $oldData,
						'new' => $newData,
						'triggerer_id' => auth()->user()?->id ?? null,
					]);
				}
			} catch (Exception $e) {
				info('OLD - ' . json_encode($oldData, JSON_THROW_ON_ERROR));
				info('NEW - ' . json_encode($newData, JSON_THROW_ON_ERROR));

				return false;
			}
		}

		return true;
	}

	public static function getReservationValue($key, $value)
	{
		if ($value === null) {
			return __('reservation.none');
		}
		$result = match ($key) {
			'discount_code_id' => DiscountCode::findOrFail($value)->display_name,
			'special_offer_id' => SpecialOffer::findOrFail($value)->display_name,
			'reservation_type_id' => ReservationType::findOrFail($value)->name,
			'status' => ReservationSlotStatus::from($value)->locale(),
			'cancelation_type' => ucfirst(ReservationSlotCancelationType::from($value)->locale()),
			'canceler_id' => User::find($value)->first_name . ' ' . User::find($value)->last_name,
			'settlement_id',
			'canceled_reason',
			'rate_service',
			'rate_staff',
			'rate_atmosphere',
			'rate_content',
			'customer_note',
			'club_note'
				=> $value,
			'customer_id' => Customer::findOrFail($value)->display_name,
			'payment_method_id' => PaymentMethod::findOrFail($value)->name .
				'(' .
				(PaymentMethod::findOrFail($value)->online ? 'online' : 'offline') .
				')',
			'slot_id' => Slot::findOrFail($value)->name,
			'start_at', 'end_at', 'canceled_at' => Timezone::convertToLocal($value)->format('Y-m-d H:i:s'),
			'price', 'final_price' => number_format($value / 100, 2, ',', ' '),
			'presence', 'club_reservation', 'occupied_status' => $value ? __('main.yes') : __('main.no'),
			default => $value,
		};

		return $result;
	}

	public function changable(): BelongsTo
	{
		return $this->morphTo();
	}

	// get reservation change single value depending on given key

	public function triggerer(): BelongsTo
	{
		return $this->belongsTo(User::class, 'triggerer_id', 'id');
	}
}
