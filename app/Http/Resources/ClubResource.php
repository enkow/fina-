<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use App\Models\Country;

class ClubResource extends JsonResource
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
		$widgetCountries = [];

		foreach ($this->widget_countries ?? [] as $countryCode) {
			$country = Country::getCached()
				->where('code', $countryCode)
				->first();
			$widgetCountries[] = [
				'code' => $country->code,
				'locale' => $country->locale,
			];
		}

		if (!count($widgetCountries)) {
			$widgetCountries[] = [
				'code' => $this->country->code,
				'locale' => $this->country->locale,
			];
		}

		return [
			'id' => $this->id,
			'name' => $this->name,
			'slug' => $this->slug,
			'description' => $this->description,
			'widget_enabled' => $this->widget_enabled,
			'widget_countries' => $widgetCountries,
			'calendar_enabled' => $this->calendar_enabled,
			'aggregator_enabled' => $this->aggregator_enabled,
			'sets_enabled' => $this->sets_enabled,
			'customer_registration_required' => $this->customer_registration_required,
			'offline_payments_enabled' => $this->offline_payments_enabled,
			'online_payments_enabled' => $this->online_payments_enabled->value,
			'created_at' => $this->created_at->format('Y-m-d H:i:s'),

			'openingHours' => OpeningHoursResource::collection($this->whenLoaded('openingHours')),
			'openingHoursExceptions' => OpeningHoursExceptionResource::collection(
				$this->whenLoaded('openingHoursExceptions')
			),
			'settings' => SettingResource::collection($this->whenLoaded('settings')),
			'country' => new CountryResource($this->whenLoaded('country')),
			'slots' => SlotResource::collection($this->whenLoaded('slots')),
			'sets' => SetResource::collection($this->whenLoaded('sets')),
			'discountCodes' => DiscountCodeResource::collection($this->whenLoaded('discountCodes')),
			'specialOffers' => SpecialOfferResource::collection($this->whenLoaded('specialOffers')),
			'announcements' => AnnouncementResource::collection($this->whenLoaded('announcements')),
			'pricelists' => PricelistResource::collection($this->whenLoaded('pricelists')),
			'games' => GameResource::collection($this->whenLoaded('games')),
			'paymentMethod' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
			'products' => ProductResource::collection(
				!request()->routeIs(['widget.*', 'new-widget.*', 'calendar.*'])
					? $this->whenLoaded('products')
					: collect([])
			),

			'sms_notifications_online' => $this->sms_notifications_online,
			'sms_notifications_offline' => $this->sms_notifications_offline,
			'customer_verification_type' => $this->customer_verification_type,

			'sms_price_online' => $this->sms_price_online,
			'sms_price_offline' => $this->sms_price_offline,
			'verification_type' => $this->verification_type,

			'preview_mode' => $this->preview_mode,
			'invoice_lang' => $this->invoice_lang,
			'invoice_autosend' => $this->invoice_autosend,
			'invoice_autopay' => $this->invoice_autopay,

			'subscription_active' => $this->subscription_active,

			'invoice_payment_time' => $this->invoice_payment_time,
			'invoice_last' => $this->invoice_last,
			'invoice_advance_payment' => $this->invoice_advance_payment,
			'invoice_next_month' => $this->invoice_next_month,
			'invoice_next_year' => $this->invoice_next_year,
			'vat' => $this->vat,

			$this->mergeWhen(
				auth()
					->user()
					?->isType('admin'),
				[
					'id' => $this->id,
					'email' => $this->email,
					'address' => $this->address,
					'postal_code' => $this->postal_code,
					'city' => $this->city,
					'phone_number' => $this->phone_number,
					'vat_number' => $this->vat_number,
					'invoice_emails' => $this->invoice_emails,
					'sms_count_online_monthly' =>
						auth()
							->user()
							?->isType('admin') && request()->routeIs('admin.clubs.show')
							? $this->sms_count['month']['online']
							: null,
					'sms_count_offline_monthly' =>
						auth()
							->user()
							?->isType('admin') && request()->routeIs('admin.clubs.show')
							? $this->sms_count['month']['offline']
							: null,
				]
			),
			$this->mergeWhen(
				auth()
					->user()
					?->isType(['admin', 'manager']),
				[
					'users' => UserResource::collection($this->whenLoaded('users')),
					'customers' => UserResource::collection($this->whenLoaded('customers')),
				]
			),
			$this->mergeWhen((bool) auth()->user(), [
				'panel_enabled' => $this->panel_enabled,
				'timer_enabled' => $this->timer_enabled,
				'first_login_message' => $this->first_login_message,
				'first_login_message_showed' => (bool) $this->first_login_message_showed,
				'tags' => TagResource::collection($this->whenLoaded('tags')),
				'reservationTypes' => ReservationTypeResource::collection(
					$this->whenLoaded('reservationTypes')
				),
				'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
			]),
		];
	}
}
