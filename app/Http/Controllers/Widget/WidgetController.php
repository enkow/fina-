<?php

namespace App\Http\Controllers\Widget;

use App\Custom\Color;
use App\Enums\ReservationSlotStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Club\StoreReservationRequest;
use App\Http\Requests\Widget\GetClubOpeningHoursForDateRequest;
use App\Http\Resources\AgreementResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\SlotResource;
use App\Models\Club;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Feature;
use App\Models\Game;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\TablePreference;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WidgetController extends Controller
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function getCoutriesNeededToWidget(Club $club)
    {
        if($club->widget_countries) {
            return Country::whereIn('code', $club->widget_countries)
                ->where('active', true)
                ->get();
        } else {
           return Country::where('active', true)->get();
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonException
     */
    public function index(Club $club)
    {
        $customerId = session()->has('customer_id') ? session()->get('customer_id') : null;
        $customer = $customerId ? Customer::getCustomer($customerId) : null;
        if ($customer && $customer->verified === false) {
            session()->forget('customer_id');
            return redirect()->refresh();
        }

        // If the logged in customer is assigned to another club, log him out
        if (($customer && $customer->club_id !== $club->id) || !$club->customer_registration_required) {
            session()->forget('customer_id');
            $customer = null;
        }

        $agreements = $club->getAgreements()->where('active', true);
        $gamesData = Game::getWidgetGamesData($club);

        $clubGames = $club->getGames();
        $featuresData = Cache::remember(
            'club:' . $club->id . ':widget_features_data',
            config('cache.model_cache_time'),
            function () use ($club, $gamesData, $clubGames) {
                $featuresData = [];
                foreach (Game::getCached() as $game) {
                    if (count($clubGames->where('id', $game->id))) {
                        foreach ($game->features as $feature) {
                            $featuresData[$feature->id] = $feature->getWidgetData([
                                'club' => $club,
                                'gamesData' => $gamesData,
                            ]);
                        }
                    }
                }
                return $featuresData;
            }
        );

        // If we have 2023-07-07 00:07 and the club is open on 2023-07-06 until 2am then we allow the customer to book for the date 2023-07-06
        $canMakePreviousDateReservation = (bool) count(
            $club->getAvailableStartHoursForDate(now()->subDay(), 30)
        );

        $props = $club->getWidgetProps($customer, [
            'clubAddress' => $club->address,
            'availableSteps' => $club->getAvailableWidgetSteps(),
            'canMakePreviousDateReservation' => $canMakePreviousDateReservation,
            'agreements' => AgreementResource::collection($agreements),
            'gamesData' => $gamesData,
            'featuresData' => $featuresData,
            'channel' => Customer::getWidgetChannel($customer),
            'locale' => session()->get(
                'customLocale',
                $customer?->locale ??
                Country::getCached()
                    ->where('code', strtoupper($club->widget_countries[0] ?? $club->country->code))
                    ->first()->locale
            ),
        ]);

        // If the club does not register customers, we enforce offline booking limits based on the customer's IP address
        if ($club->customer_registration_required === false) {
            $props[
            'customer_offline_today_reservations_count'
            ] = Customer::getAnonymousCustomerActiveOfflineReservationsCounts($club);
            $props[
            'customer_online_active_reservations_count'
            ] = Customer::getAnonymousCustomerActiveOnlineReservationsCount();
        }

        return Inertia::render('Widget-3/Widget3View', $props);
    }
    public function newWidget(Club $club)
    {
        $customerId = session()->has('customer_id') ? session()->get('customer_id') : null;
        $customer = $customerId ? Customer::find($customerId) : null;
        if ($customer && $customer->verified === false) {
            session()->forget('customer_id');
            return redirect()->refresh();
        }

        // If the logged in customer is assigned to another club, log him out
        if (($customer && $customer->club_id !== $club->id) || !$club->customer_registration_required) {
            session()->forget('customer_id');
            $customer = null;
        }

        $agreements = $club->getAgreements()->where('active', true);
        $gamesData = Game::getWidgetGamesData($club);

        $clubGames = $club->getGames();
        $featuresData = Cache::remember(
            'club:' . $club->id . ':widget_features_data',
            config('cache.model_cache_time'),
            function () use ($club, $gamesData, $clubGames) {
                $featuresData = [];
                foreach (Game::getCached() as $game) {
                    if (count($clubGames->where('id', $game->id))) {
                        foreach ($game->features as $feature) {
                            $featuresData[$feature->id] = $feature->getWidgetData([
                                'club' => $club,
                                'gamesData' => $gamesData,
                            ]);
                        }
                    }
                }
                return $featuresData;
            }
        );

        // If we have 2023-07-07 00:07 and the club is open on 2023-07-06 until 2am then we allow the customer to book for the date 2023-07-06
        $canMakePreviousDateReservation = (bool) count(
            $club->getAvailableStartHoursForDate(now()->subDay(), 30)
        );

        $props = $club->getWidgetProps($customer, [
            'clubAddress' => $club->address,
            'availableSteps' => $club->getAvailableWidgetSteps(),
            'countries' => CountryResource::collection(Country::where('active', true)->get()),
            'canMakePreviousDateReservation' => $canMakePreviousDateReservation,
            'agreements' => AgreementResource::collection($agreements),
            'gamesData' => $gamesData,
            'featuresData' => $featuresData,
            'channel' => Customer::getWidgetChannel($customer),
            'locale' => session()->get(
                'customLocale',
                $customer?->locale ?? ($club->widget_locales[0] ?? $club->country->locale)
            ),
            'calendarColors' => Color::createTailwindPallete(
                Setting::getClubGameSetting($club->id, 'widget_color')['value']
            ),
        ]);

        // If the club does not register customers, we enforce offline booking limits based on the customer's IP address
        if ($club->customer_registration_required === false) {
            $props[
            'customer_offline_today_reservations_count'
            ] = Customer::getAnonymousCustomerActiveOfflineReservationsCounts($club);
            $props[
            'customer_online_active_reservations_count'
            ] = Customer::getAnonymousCustomerActiveOnlineReservationsCount();
        }

        return Inertia::render('Widget-2/Widget2View', $props);
    }

    public function calendar(Club $club): Response|RedirectResponse
    {
        $customerId = session()->has('customer_id') ? session()->get('customer_id') : null;
        $customer = $customerId ? Customer::find($customerId) : null;
        if ($customer && $customer->verified === false) {
            session()->forget('customer_id');
            return redirect()->refresh();
        }

        // If the logged in customer is assigned to another club, log him out
        if (($customer && $customer->club_id !== $club->id) || !$club->customer_registration_required) {
            session()->forget('customer_id');
            $customer = null;
        }

        $agreements = $club->getAgreements()->where('active', true);
        $gamesData = Game::getWidgetGamesData($club);

        $featuresData = [];
        foreach (Club::getCachedGamesForSettings($club) as $game) {
            foreach ($game->features as $feature) {
                $featuresData[$feature->id] = $feature->getWidgetData([
                    'club' => $club,
                    'gamesData' => $gamesData,
                ]);
            }
        }

        //Filter array if its value is empty array then remove
        $featuresData = array_filter($featuresData);

        // If we have 2023-07-07 00:07 and the club is open on 2023-07-06 until 2am then we allow the customer to book for the date 2023-07-06
        $canMakePreviousDateReservation = (bool) count(
            $club->getAvailableStartHoursForDate(now()->subDay(), 30)
        );
        $props = $club->getWidgetProps($customer, [
            'clubAddress' => $club->address,
            'announcements' => AnnouncementResource::collection($club->announcements()->where('start_at', '>=',
                Carbon::today())->where('end_at', '>=', Carbon::now())->where('start_at', '<',
                Carbon::tomorrow())->get()),
            'countries' => CountryResource::collection(self::getCoutriesNeededToWidget($club)),
            'availableSteps' => $club->getAvailableWidgetSteps(),
            'canMakePreviousDateReservation' => $canMakePreviousDateReservation,
            'agreements' => AgreementResource::collection($agreements),
            'gamesData' => $gamesData,
            'featuresData' => $featuresData,
            'channel' => Customer::getWidgetChannel($customer),
            'locale' => session()->get(
                'customLocale',
                $customer?->locale ?? ($club->widget_locales[0] ?? $club->country->locale)
            ),
            'calendarColors' => Color::createTailwindPallete(
                Setting::getClubGameSetting($club->id, 'widget_color')['value']
            ),
        ]);

        // If the club does not register customers, we enforce offline booking limits based on the customer's IP address
        if ($club->customer_registration_required === false) {
            $props[
            'customer_offline_today_reservations_count'
            ] = Customer::getAnonymousCustomerActiveOfflineReservationsCounts($club);
            $props[
            'customer_online_active_reservations_count'
            ] = Customer::getAnonymousCustomerActiveOnlineReservationsCount();
        }

        return Inertia::render('Widget-1/Widget1View', $props);
    }

    public function getAvailableStartAtDates(Request $request, Club $club): array
    {
        $data = $request->all();
        $data['club_id'] = $club->id;
        $data['game_id'] = $data['game_id'] ?? Game::getCached()->first()->id;
        $gameFeatures = Game::find($data['game_id'])->features;
        foreach ($gameFeatures as $feature) {
            if (!$feature->executablePublicly) {
                continue;
            }
            $data = $feature?->prepareDataForSlotSearch($data) ?? $data;
        }

        $result = [];
        foreach (
            $club->getAvailableStartHoursForDate($data['start_at'], $data['duration'], 'reservation')
            as $datetime
        ) {
            $vacantSlots = Slot::getAvailableOptimized(
                array_merge($data, [
                    'start_at' => $datetime,
                    'vacant' => true,
                    'active' => true,
                ]),
                $gameFeatures
            );

            // there is not enough vacant slot for this time slot to show it
            if (count($vacantSlots) < $data['slots_count']) {
                continue;
            }
            $result['times'][] = $datetime;
        }
        $priceList = Pricelist::where('club_id', $club->id)->where('game_id', $data['game_id'])->where('deleted_at', null)->first();
      $day = Carbon::parse($data['start_at'])->dayOfWeek;
      if($day == 0) {
          $day = 7;
      }
      $priceListItems = PricelistItem::where('pricelist_id',$priceList->id)->where('day', $day)->where('deleted_at', null)->first()->price;
      $result['isFreeDay'] = $priceListItems == 0;
      return $result;
    }

    public function getClubOpeningHoursForDate(
        GetClubOpeningHoursForDateRequest $request,
        Club $club
    ): JsonResponse {
        return response()->json($club->getOpeningHoursForDate($request->get('date')));
    }

    public function calculatePrice(Request $request, Club $club): JsonResponse|array
    {
        return Reservation::calculatePrice($request, 'json');
    }

    public function store(Club $club): JsonResponse|RedirectResponse
    {
        $request = request();
        $storeReservationRequest = new StoreReservationRequest();
        $storeReservationRequest->prepareForValidation();
        if (!$club->customer_registration_required || !session()->has('customer_id')) {
            $offlineTodayReservationsCounts = Customer::getAnonymousCustomerActiveOfflineReservationsCounts(
                $club
            );
            $onlineActiveReservationsCount = Customer::getAnonymousCustomerActiveOnlineReservationsCount();
        } else {
            $customer = Customer::getCustomer(session()->get('customer_id'));
            $customer->update([
                'locale' =>
                    session()->get('customLocale', $club->widget_default_locale) ??
                    ($request->all()['customer']['locale'] ?? 'en'),
            ]);
            session()->forget('customLocale');

            $onlineActiveReservationsCount = $customer->online_active_reservations_count;
            $offlineTodayReservationsCounts = $customer->offline_today_reservations_counts;
        }



        if ($onlineActiveReservationsCount >= 3 && $request->all()['payment_type'] === 'online') {
            return response()->json([
                'reservationData' => null,
                'paymentUrl' => null,
                'errorKey' => 1,
            ]);
        }

        $game = Game::find($request->game_id);

        if($game->hasFeature('has_offline_reservation_limits_settings') && $request->all()['payment_type'] !== 'online') {
            $offlineLimit = Setting::getClubGameSetting($club->id, 'offline_reservation_daily_limit' ,$game->id)['value'];;
            $todayOfflineLimit = $offlineLimit[weekDay($request->all()['start_at']) - 1] ?? 100;
            $offlineTodayReservations = $offlineTodayReservationsCounts[$game->id] ?? 0;
            if (
                $offlineTodayReservations > $todayOfflineLimit &&
                $request->all()['payment_type'] === 'offline'
            ) {
                return response()->json([
                    'reservationData' => null,
                    'paymentUrl' => null,
                    'errorKey' => 101,
                ]);
            }
        }

        if($game->hasFeature('has_offline_reservation_limits_settings') && $request->all()['payment_type'] !== 'online'){
            $slotLimit = Setting::getClubGameSetting($club->id, 'offline_reservation_slot_limit' ,$game->id)['value'];;
            if(count($request->slot_ids) > $slotLimit[weekDay($request->all()['start_at']) - 1] && $slotLimit[weekDay($request->all()['start_at']) - 1] != null){
                return response()->json([
                    'reservationData' => null,
                    'paymentUrl' => null,
                    'errorKey' => 102,
                ]);
            }
        }

        $data = $request->all();
        $data['club_id'] = $club->id;
        $data['customer_ip'] = request()->ip();
        $reservation = Reservation::store(
            data: $data,
            returnVariable: 'reservation',
            reservationSource: 0,
            withValidation: true,
            widget: true
        );

        $paymentMethod = PaymentMethod::getPaymentMethod($reservation->payment_method_id);
        $paymentUrl = $paymentMethod->online
            ? Payment::for($reservation, $paymentMethod, [
                'firstReservationSlot' => $reservation->reservationSlots->first(),
            ])->getUrl(route('widget.payment-status', ['club' => $club]), [
                'reservation' => $reservation,
                'firstReservationSlot' => $reservation->reservationSlots->first(),
            ])
            : null;

        return response()->json([
            'reservationData' => TablePreference::getDataArrayFromModel(
                $reservation->prepareForOutput(false, ['reservationSlots' => $reservation->reservationSlots]),
                array_merge(
                    Reservation::tableData(gameId: $reservation->game_id)['preference'],

                    // load extended reservation data, we need more data than showed in reservation tables
                    [['key' => 'extended', 'enabled' => true]]
                )
            ),
            'paymentUrl' => $paymentUrl,
        ]);
    }

    public function paymentProcessEnded(Club $club): Response
    {
        return Inertia::render('Widget-3/PaymentStatus', $club->getWidgetProps(null, []));
    }

    public function setCountryByLocaleCode(Club $club, string $localeCode): RedirectResponse
    {
        $country = Country::getCached()
            ->where('locale', $localeCode)
            ->first();
        if (!empty($country)) {
            session()->put('customLocale', $localeCode);
            session()->put('customCountry', $country->code);
        }

        return redirect()->back();
    }
}
