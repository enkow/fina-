<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SetResource extends JsonResource
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
			'name' => $this->name,
			'active' => $this->active,
			'mobile_photo' => $this->mobile_photo,
			'photo' => $this->photo,
			'description' => $this->description,
			'price' => $this->price,
			'quantity' => $this->quantity,
			'reservation_slots_count' => $this->whenCounted('reservationSlots'),

			'club' => new ClubResource($this->whenLoaded('club')),

			$this->mergeWhen((bool) auth()->user(), [
				'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
				'creator' => new UserResource($this->whenLoaded('creator')),
				'created_at' => $this->created_at->format('Y-m-d H:i:s'),
			]),
		];
	}
}
