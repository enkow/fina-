<?php

namespace App\Http\Middleware;

use App\Models\Country;
use App\Models\Customer;
use App\Models\ReservationNumber;
use Closure;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SetLocaleAndTimezone
{
	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		$club = $request->route('club') ?? (club() ?? null);
		if ($club) {
			$country = Country::getCountry($club->country_id);
			date_default_timezone_set($country->timezone);
		}

		$reservationNumber = null;
		if (request()->route('reservationNumber')) {
			$reservationNumber = is_string(request()->route('reservationNumber'))
				? ReservationNumber::fromNumber(request()->route('reservationNumber'))
				: request()->route('reservationNumber');
		}

		if (request()->routeIs('widget.*')) {
			$countryCode = match (true) {
				session()->has('customer_id') &&
					Customer::getCustomer(session()->get('customer_id', null)) &&
					!session()->has('customCountry')
					=> Customer::getCustomer(session()->get('customer_id', null))
					->locale, // customer is logged but no custom lanugage is selected
				session()->get('customCountry') &&
					request()->routeIs('widget.*') &&
					(bool) Country::getCached()
						->where('active', 1)
						->where('code', session()->get('customCountry'))
						->first()
					=> session()->get(
					'customCountry'
				), // the customer has selected their preferred locale and is on the booking plugin
				$reservationNumber => $reservationNumber->numerable->reservation
					->locale, // we display one of the booking management pages on the booking plugin (e.g. cancellation)
				!empty($club)
					=> $club->default_widget_locale, // the customer has not selected their preferred locale and is on the booking plugin
			};
			$country = Country::getCached()
				->where('active', 1)
				->where('code', $countryCode)
				->first();

			app()->setLocale($country->locale);
			session()->put('customCountry', $country->code);
		} else {
			app()->setLocale(
				match (true) {
					!empty($club) => $country->locale, // Locale in the club panel
					default => config('app.locale'),
				}
			);
		}

		return $next($request);
	}
}
