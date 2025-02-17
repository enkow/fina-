<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ManagerEmailResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  Request  $request
	 *
	 * @return array|Arrayable|JsonSerializable
	 */
	public function toArray($request): array
	{
		return [
			'id' => $this->id,
			'game_id' => $this->game_id,
			'email' => $this->email,

			'club' => new ClubResource($this->whenLoaded('club')),
		];
	}
}
