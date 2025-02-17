<?php

namespace App\Http\Resources;

use App\Custom\Timezone;
use App\Enums\ReservationSlotStatus;
use App\Models\Club;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;
use JsonSerializable;

class CustomerResource extends JsonResource
{
	private $withoutFields;

	public function __construct($resource, $withoutFields = [])
	{
		$this->withoutFields = is_array($withoutFields) ? $withoutFields : [];
		parent::__construct($resource);
	}

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
			'encryptedId' => Crypt::encrypt($this->id),
			'locale' => $this->locale,
			'online' => (bool) $this->password,
			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'tags' => $this->when(
					(bool) auth()->user(),
					TagResource::collection($this->whenLoaded('tags'))
				),
				'tags_string' => $this->tags_string,
				'full_name' => ($this->first_name ?? "") . ' ' . ($this->last_name ?? ""),
				'first_name' => $this->first_name ?? "",
				'last_name' => $this->last_name ?? "",
				'agreements' => $this->whenLoaded('agreements'),
			]),
			$this->mergeWhen(auth()->user() || session()->get('customer_id') === $this->id, [
				'email' => $this->email,
				'club_name' => $this->club_name,
				'verified' => $this->verified ?? false,
				'created_at' => $this->created_at,
				'club' => new ClubResource($this->whenLoaded('club')),
				'reservations_max_created_at' => $this->reservations_max_created_at
					? Timezone::convertToLocal($this->reservations_max_created_at)->format('Y-m-d H:i:s')
					: null,
				'latestReservation' => new ReservationResource($this->whenLoaded('latestReservation')),
				'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
				'reservations_count' => $this->whenCounted('reservations'),
				'phone' => $this->phone,
			]),
			$this->mergeWhen(session()->get('customer_id') === $this->id, [
				'offline_today_reservations_counts' => !in_array(
					'offline_today_reservations_count',
					$this->withoutFields,
					true
				)
					? $this->offline_today_reservations_counts
					: null,
				'online_active_reservations_count' => !in_array(
					'online_active_reservations_count',
					$this->withoutFields,
					true
				)
					? $this->online_active_reservations_count
					: null,
			]),
			$this->mergeWhen(request()->routeIs('widget.*'), [
				'agreements_to_consent' =>
					!in_array('agreements_to_consent', $this->withoutFields, true) &&
					session()->has('customer_id')
						? AgreementResource::collection(
							Club::getClub($this->club_id)
								->agreements()
								->required()
								->whereDoesntHave('customers', function ($query) {
									$query->where('customers.id', session()->get('customer_id'));
								})
								->get()
						)
						: null,
				'widget_channel' => $this->widget_channel,
			]),
		];
	}
}
