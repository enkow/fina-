<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SpecialOfferResource extends JsonResource
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
			'active' => $this->active,
			'active_by_default' => $this->active_by_default,
			'name' => $this->name,
			'display_name' => $this->display_name,
			'description' => $this->description,
			'value' => $this->value,
			'active_week_days' => $this->active_week_days,
			'duration' => $this->duration
				? str_pad((int) ($this->duration / 60), 2, '0', STR_PAD_LEFT) .
					':' .
					str_pad($this->duration % 60, 2, '0', STR_PAD_LEFT)
				: null,
			'time_range' => $this->time_range,
			'time_range_type' => $this->time_range_type,
			'slots' => $this->slots,
			'when_applies' => $this->when_applies,
			'applies_default' => $this->applies_default,
			'when_not_applies' => $this->when_not_applies,
			'photo' => $this->photo,

			'game_id' => $this->game_id,
			'game' => new GameResource($this->whenLoaded('game')),

			$this->mergeWhen(auth()->user(), [
				'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
				'club' => new ClubResource($this->whenLoaded('club')),
				'created_at' => $this->created_at->format('Y-m-d H:i:s'),
				'creator' => new UserResource($this->whenLoaded('creator')),
			]),
		];
	}
}
