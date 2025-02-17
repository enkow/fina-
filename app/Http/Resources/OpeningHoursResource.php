<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class OpeningHoursResource extends JsonResource
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
			'day' => $this->day,
			'club_start' => substr($this->club_start, 0, strlen($this->club_start) - 3),
			'club_end' =>
				($clubEnd = substr($this->club_end, 0, strlen($this->club_end) - 3)) === '23:59'
					? '24:00'
					: $clubEnd,
			'club_closed' => $this->club_closed,
			'open_to_last_customer' => $this->open_to_last_customer,
			'reservation_start' => substr($this->reservation_start, 0, strlen($this->reservation_start) - 3),
			'reservation_end' =>
				($reservationEnd = substr($this->reservation_end, 0, strlen($this->reservation_end) - 3)) ===
				'23:59'
					? '24:00'
					: $reservationEnd,
			'reservation_closed' => $this->reservation_closed,

			'club' => new ClubResource($this->whenLoaded('club')),

			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'created_at' => $this->created_at,
			]),
		];
	}
}
