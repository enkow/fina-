<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserResource extends JsonResource
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
				'type' => $this->type,
				'first_name' => $this->first_name,
				'last_name' => $this->last_name,
				'email' => $this->email,
				'sidebar_reduced' => $this->sidebar_reduced,
				'selected_country_id' => $this->selected_country_id,
				'created_at' => $this->created_at,

				'club' => new ClubResource($this->whenLoaded('club')),
			]),
			$this->mergeWhen(
				auth()
					->user()
					?->isType('manager'),
				[
					'last_login' => $this->last_login?->format('Y-m-d H:i:s'),
				]
			),
		];
	}
}
