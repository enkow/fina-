<?php

namespace App\Http\Requests\Club;

use App\Models\ReservationNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelReservationRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return ReservationNumber::find(request()->get('reservationNumber'))?->numerable->firstReservationSlot
			->slot->pricelist->club_id === clubId();
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'reservationNumber' => 'required',
			'reasonType' => [
				'required',
				'numeric',
				'min:0',
				Rule::in(array_keys(__('reservation.cancelation-types'))),
			],
			'reasonContent' => ['required_if:reasonType,0'],
			'cancelRelatedReservations' => ['nullable', 'boolean'],
		];
	}
}
