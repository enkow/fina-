<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AnnouncementResource extends JsonResource
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
			'game_id' => $this->game_id,
			'type' => $this->type,
			'content' => $this->content,
			'content_top' => $this->content_top,
			'content_bottom' => $this->content_bottom,

			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'club' => new ClubResource($this->whenLoaded('club')),
				'start_at' => $this->start_at,
				'end_at' => $this->end_at,
			]),
		];
	}
}
