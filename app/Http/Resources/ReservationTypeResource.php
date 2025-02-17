<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ReservationTypeResource extends JsonResource
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
			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'name' => $this->name,
				'color' => $this->color,
				'created_at' => $this->created_at,

				'club' => new ClubResource($this->whenLoaded('club')),
				'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
			]),
		];
	}
}
