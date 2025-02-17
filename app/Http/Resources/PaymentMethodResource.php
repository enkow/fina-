<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PaymentMethodResource extends JsonResource
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
			'code' => $this->code,
			'type' => $this->type,
			'club_id' => $this->club_id,
			'activated' => $this->activated,
			'online' => $this->online,
			'adminTab' => $this->resource::ADMIN_TAB,
			'globalSettings' => $this->resource::GLOBAL_SETTINGS,
			'credentials' => $this->credentials,
			'fee_percentage' => $this->fee_percentage,
			'fee_fixed' => $this->fee_fixed,

			'country' => new CountryResource($this->whenLoaded('country')),

			$this->mergeWhen((bool) auth()->user(), [
				'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
			]),
		];
	}
}
