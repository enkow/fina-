<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ReservationChangeResource extends JsonResource
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
			$this->mergeWhen(auth()->user(), [
				'reservation' => new ReservationResource($this->whenLoaded('reservation')),
				'data' => $this->data,
				'created_at' => $this->created_at,
			]),
		];
	}
}
