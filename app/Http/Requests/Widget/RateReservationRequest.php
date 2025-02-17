<?php

namespace App\Http\Requests\Widget;

use App\Models\ReservationNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class RateReservationRequest extends FormRequest
{
	public function authorize(): bool
	{
		$reservationNumber = ReservationNumber::find($this->route('reservationNumber'));
		$customerId = Crypt::decrypt($this->route('encryptedCustomerId'));

		return $reservationNumber->numerable->reservation->customer_id === $customerId;
	}

	public function prepareForValidation(): void
	{
		$this->merge([
			'rate_service' => (int) $this->get('rate_service'),
			'rate_atmosphere' => (int) $this->get('rate_atmosphere'),
			'rate_staff' => (int) $this->get('rate_staff'),
		]);
	}

	public function rules(): array
	{
		if ($this->isMethod('GET')) {
			return [];
		}

		$rateValidationRules = ['integer', 'min:1', 'max:5'];

		return [
			'rate_service' => $rateValidationRules,
			'rate_atmosphere' => $rateValidationRules,
			'rate_staff' => $rateValidationRules,
			'rate_content' => 'nullable|string|max:400',
		];
	}
}
