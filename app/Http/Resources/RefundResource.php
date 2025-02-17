<?php

namespace App\Http\Resources;

use App\Custom\Timezone;
use App\Models\Club;
use App\Models\PaymentMethod;
use App\Models\Pricelist;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RefundResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  Request  $request
	 *
	 * @return array|Arrayable|JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'club' => new ClubResource(
				auth()
					->user()
					->isType(['manager', 'employee'])
					? club()
					: Club::getClub(
						Pricelist::getPricelist($this->firstReservationSlot->slot->pricelist_id)->club_id
					)
			),
			'customer' => new CustomerResource($this->firstReservationSlot->reservation->customer, [
				'offline_today_reservations_count',
				'online_active_reservations_count',
				'agreements_to_consent',
			]),
			'club_payment_gateway' => (bool) PaymentMethod::getPaymentMethod(
				$this->firstReservationSlot->reservation->payment_method_id
			)->club_id,
			'status' => $this->status,
			'approver_id' => $this->approver_id,
			'approver' => new UserResource($this->whenLoaded('approver')),
			'approved_at' => Timezone::convertToLocal($this->approved_at)?->format('Y-m-d H:i'),
			'price' => $this->price,
			'reservation_numbers' => $this->reservation_numbers,
			'created_at' => Timezone::convertToLocal($this->created_at)?->format('Y-m-d H:i:s'),
			'cancelation_type' => $this->cancelation_type,
		];
	}
}
