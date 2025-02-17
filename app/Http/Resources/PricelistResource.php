<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PricelistResource extends JsonResource
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
			'game' => new GameResource($this->whenLoaded('game')),
			'club' => new ClubResource($this->whenLoaded('club')),

			'slots' => SlotResource::collection($this->whenLoaded('slots')),
			'pricelistItems' => PricelistItemResource::collection($this->whenLoaded('pricelistItems')),
			'pricelistExceptions' => PricelistExceptionResource::collection(
				$this->whenLoaded('pricelistExceptions')
			),

			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'name' => $this->name,
				'created_at' => $this->created_at,
			]),
		];
	}
}
