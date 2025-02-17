<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SettlementResource extends JsonResource
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
			$this->mergeWhen(
				auth()
					->user()
					?->isType(['admin', 'manager']),
				[
					'id' => $this->id,
					'club' => new ClubResource($this->whenLoaded('club')),
					'status' => $this->status,
					'created_at' => $this->created_at,
				]
			),
		];
	}
}
