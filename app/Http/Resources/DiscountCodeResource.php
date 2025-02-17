<?php

namespace App\Http\Resources;

use App\Enums\DiscountCodeType;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class DiscountCodeResource extends JsonResource
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
			'code' => $this->code,
			'type' => $this->type,
			'value' => $this->type === DiscountCodeType::Amount ? $this->value / 100 : $this->value,
			'club' => new ClubResource($this->whenLoaded('club')),
			'game_id' => $this->game_id,
			'game' => new GameResource($this->whenLoaded('game')),
			'display_name' => $this->display_name,

			$this->mergeWhen((bool) auth()->user(), [
				'code_quantity' => $this->code_quantity,
				'code_quantity_per_customer' => $this->code_quantity_per_customer,
				'reservations_count' => $this->whenCounted('reservations'),
				'creator' => new UserResource($this->whenLoaded('creator')),
				'created_at' => $this->created_at->isoFormat('L, LT'),
				'start_at' => $this->start_at ? $this->start_at->format('Y-m-d H:i') : null,
				'end_at' => $this->end_at ? $this->end_at->format('Y-m-d H:i') : null,
			]),

			$this->mergeWhen(request()->get('start_at', false) && request()->get('duration', false), [
				'is_available' => $this->isAvailable(
					request()->get('customer_id'),
					request()->get('start_at'),
					Carbon::parse(request()->get('start_at'))
						->addMinutes(request()->get('duration'))
						->format('Y-m-d H:i:s')
				),
			]),
		];
	}
}
