<?php

namespace App\Http\Requests\Widget;

use App\Models\ReservationNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class CancelReservationRequest extends FormRequest
{
	public function authorize(): bool
	{
		$reservationNumber = ReservationNumber::find($this->route('reservationNumber'));
		$customerId = $this->route('encryptedCustomerId');

		return $reservationNumber->numerable->reservation->customer_id === Crypt::decrypt($customerId);
	}

	public function rules(): array
	{
		return [];
	}
}
