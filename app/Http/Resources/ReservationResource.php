<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ReservationResource extends JsonResource
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
			'start_at' => $this->start_at,
			'end_at' => $this->end_at,
			'status' => $this->status,
			'discount_code' => new DiscountCodeResource($this->whenLoaded('discount_code')),
			'discount_code_value' => $this->discount_code_value,
			'special_offer' => new SpecialOfferResource($this->whenLoaded('special_offer')),
			'special_offer_value' => $this->special_offer_value,
			'rate_service' => $this->rate_service,
			'rate_atmosphere' => $this->rate_atmosphere,
			'rate_staff' => $this->rate_staff,
			'rate_content' => $this->rate_content,
			'created_at' => $this->created_at->format('Y-m-d H:i:s'),
			'slot' => new SlotResource($this->whenLoaded('slot')),
			'slots_count' => $this->whenCounted('slots'),
			'game_id' => $this->game_id,
			'reservationGroup' => new ReservationGroupResource($this->whenLoaded('reservationGroup')),
			'price' => $this->price,

			$this->mergeWhen((bool) auth()->user(), [
				'club_note' => $this->club_note,
				'customer_note' => $this->customer_note,
				'canceler' => new UserResource($this->whenLoaded('canceler')),
				'customer' => new CustomerResource($this->whenLoaded('customer')),
				'reservationType' => new ReservationTypeResource($this->whenLoaded('reservationType')),
				'paymentMethod' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
			]),

			$this->mergeWhen(
				auth()
					->user()
					?->isType(['admin', 'manager']),
				[
					'settlement' => new SettlementResource($this->whenLoaded('settlement')),
					'reservationChanges' => ReservationChangeResource::collection(
						$this->whenLoaded('reservationChanges')
					),
					'payment_token' => $this->payment_token,
				]
			),
		];
	}
}
