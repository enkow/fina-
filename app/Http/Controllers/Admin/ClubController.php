<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationSlotStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClubRequest;
use App\Http\Requests\Admin\UpdateClubGameCustomNamesRequest;
use App\Http\Requests\Admin\ProductToClubRequest;
use App\Http\Requests\Admin\UpdateClubRequest;
use App\Http\Requests\Admin\UpdateGameCommissionRequest;
use App\Http\Resources\ClubResource;
use App\Http\Resources\GameResource;
use App\Models\Club;
use App\Models\Game;
use App\Models\Country;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;
use App\Custom\Fakturownia;
use App\Enums\OnlinePayments;
use App\Models\BulbAdapter;
use App\Models\PaymentMethods\Stripe;
use App\Models\PaymentMethods\Tpay;

class ClubController extends Controller
{
	public function index(): Response
	{
		$clubs = ClubResource::collection(
			Club::where('country_id', auth()->user()?->country_id)
				->with('country')
				->searchable('admin_clubs', ['name', 'email', 'city'])
				->paginate(30)
		);

		return Inertia::render('Admin/Clubs/Index', compact(['clubs']));
	}

	public function store(StoreClubRequest $request): RedirectResponse
	{
		$fakturownia = new Fakturownia();

		$clubData = $request->only(['name', 'slug', 'country_id']);

		$clubData['fakturownia_id'] = $fakturownia->createClient(['name' => $request->input('name')])['id'];

		Club::create($clubData);

		auth()
			->user()
			?->update([
				'country_id' => $request->country_id,
			]);

		Cache::forget('user_' . auth()->user()->id);

		return redirect()
			->route('admin.clubs.index')
			->with('message', [
				'type' => 'success',
				'content' => 'Dodano nowy klub',
			]);
	}

	public function create(): Response
	{
		return Inertia::render('Admin/Clubs/Create');
	}

	public function update(UpdateClubRequest $request, Club $club): RedirectResponse
	{
		$club->update(
			$request->only([
				'country_id',
				'name',
				'description',
				'slug',
				'address',
				'postal_code',
				'city',
				'phone_number',
				'email',
				'vat_number',
				'invoice_emails',
				'first_login_message',
				'widget_countries',
			])
		);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano dane klubu',
			]);
	}

	/**
	 * @throws JsonException
	 */
	public function edit(Club $club): Response
	{
		$commissionResult = [];

		$billingPeriod = $club->getBillingPeriod()['month'];

		foreach (
			$club
				->games()
				->withPivot('id')
				->get()
			as $game
		) {
			$commission = Reservation::getAppCommission(
				club: $club,
				gameId: $game->pivot->game_id,
				startAt: $billingPeriod['start_at'],
				endAt: $billingPeriod['end_at']
			);

			$data = [
				'id' => $game->pivot->id,
				'name' => $game->name,
				'include_on_invoice_status' => $game->pivot->include_on_invoice_status,
				'include_on_invoice' => $game->pivot->include_on_invoice,
				'commission' => $commission,
			];

			array_push($commissionResult, $data);
		}

		$bulbsAdaptersFieldsPrepare = [];
		foreach (BulbAdapter::CHILD_TYPES as $key => $value) {
			$bulbsAdaptersFieldsPrepare[$key] = $value::ADMIN_SETTINGS;
		}

		return Inertia::render('Admin/Clubs/Edit', [
			'club' => new ClubResource($club->load(['country', 'games', 'products', 'paymentMethod'])),
			'games' => GameResource::collection(Game::all()),
			'enabledGames' => GameResource::collection(
				$club
					->games()
					->orderByPivot('weight', 'desc')
					->get()
			),
			'disabledGames' => GameResource::collection(
				Game::whereNotIn('id', $club->games->pluck('id')->toArray())->get()
			),
			'settings' => Setting::retrieve(scope: 'club', clubId: $club->id),
			'bulbsAdaptersFields' => $bulbsAdaptersFieldsPrepare,
			'products' => Product::all(),
			'commissions' => $commissionResult,
		]);
	}

	/**
	 * @throws JsonException
	 */
	public function updateClubGameData(
		UpdateClubGameCustomNamesRequest $request,
		Club $club,
		Game $game
	): RedirectResponse {
		$club->games()->updateExistingPivot($game, [
			'custom_names' => json_encode($request->all()[$game->id]['names'], JSON_THROW_ON_ERROR),
			'fee_fixed' => $request->all()[$game->id]['fee_fixed'],
			'fee_percent' => $request->all()[$game->id]['fee_percent'],
			'enabled_on_widget' => $request->all()[$game->id]['enabled_on_widget'],
		]);
		Cache::flush();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano nazwy gier',
			]);
	}

	public function login(Club $club): RedirectResponse
	{
		$firstManager = $club
			->users()
			->where('type', 'manager')
			->first();
		if (!empty($firstManager)) {
			session()->put('adminId', auth()->user()->id);
			Auth::login($firstManager);

			return redirect()->route('dashboard-redirect');
		}

		return redirect()
			->route('admin.clubs.index')
			->with('message', [
				'type' => 'error',
				'content' => 'Klub nie posiada konta managera, na które można się zalogować.',
			]);
	}

	public function destroy(Club $club): RedirectResponse
	{
		if (
			$club
				->reservationSlots()
				->whereIn('status', [ReservationSlotStatus::Confirmed, ReservationSlotStatus::Pending])
				->whereNull('cancelation_type')
				->where('start_at', '>=', now())
				->exists()
		) {
			return redirect()
				->route('admin.clubs.index')
				->with('message', [
					'type' => 'error',
					'content' => 'Nie można usunąć klubu, ponieważ posiada rezerwacje w przyszłości',
				]);
		}

		$club->delete();

		return redirect()
			->route('admin.clubs.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto klub',
			]);
	}

	public function toggleField(Club $club, $field): RedirectResponse
	{
		if (
			!in_array($field, [
				'panel_enabled',
				'widget_enabled',
				'calendar_enabled',
				'aggregator_enabled',
				'sets_enabled',
				'offline_payments_enabled',
				'customer_registration_required',
				'invoice_autosend',
				'invoice_advance_payment',
				'invoice_autopay',
				'invoice_last',
				'preview_mode',
				'sms_notifications_online',
				'sms_notifications_offline',
			])
		) {
			return redirect()->back();
		}

		$club->update([
			$field => !$club->$field,
		]);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano dane klubu',
			]);
	}

	public function setField(Club $club, $field): RedirectResponse
	{
		if (
			!in_array($field, [
				'online_payments_enabled',
				'invoice_lang',
				'invoice_next_year',
				'invoice_next_month',
				'invoice_payment_time',
				'vat',
				'customer_verification_type',
				'sms_price_online',
				'sms_price_offline',
			])
		) {
			return redirect()->back();
		}

		$club->update([
			$field => request()->get('value'),
		]);

		if (
			$field === 'online_payments_enabled' &&
			request()->get('value') !== OnlinePayments::Disabled->value
		) {
			if ($club->paymentMethods()->count() === 0 || !$club->paymentMethod) {
				Stripe::enable($club);
			}
		}

		$club->flushCache();

		return redirect()
			->route('admin.clubs.edit', ['club' => $club->id])
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano dane klubu',
			]);
	}

	/**
	 * @throws JsonException
	 *
	 * The method enabled or disables a selected game for the club.
	 * If the game is being enabled, we set the appropriate order of display in select inputs available in the panel,
	 * relative to other games already enabled.
	 */
	public function toggleGame(Club $club, Game $game): RedirectResponse
	{
		if ($club->games->contains($game)) {
			$club->games()->detach($game);
		} else {
			$greatestWeightGame = $club
				->games()
				->orderByPivot('weight', 'desc')
				->first();
			$greatestWeight = !empty($greatestWeightGame) ? $greatestWeightGame->pivot->weight : 0;
			$club->games()->attach($game, [
				'weight' => $greatestWeight + 1,
				'custom_names' => json_encode([], JSON_THROW_ON_ERROR),
			]);
		}
		Cache::flush();

		return redirect()->back();
	}

	/**
	 * The function swaps the position in the display queue of select inputs available in the panel with the game
	 * that is located in the next or previous position.
	 */
	public function moveGame(Club $club, Game $game, $moveType): void
	{
		$currentWeight = $club->games()->find($game)->pivot->weight;
		$borderer = $club
			->games()
			->wherePivot(
				'weight',
				match ($moveType) {
					'up' => '>',
					'down' => '<',
				},
				$currentWeight
			)
			->get();
		$borderer = match ($moveType) {
			'down' => $borderer[0],
			'up' => $borderer[count($borderer) - 1],
		};
		if (!empty($borderer)) {
			$mem = $club->games()->find($game->id)->pivot->weight;
			$club->games()->updateExistingPivot($game->id, [
				'weight' => $club->games()->find($borderer->id)->pivot->weight,
			]);
			$club->games()->updateExistingPivot($borderer->id, [
				'weight' => $mem,
			]);
		} else {
			$club->games()->updateExistingPivot($game->id, [
				'weight' => $club->games()->find($game->id)->pivot->weight + 1,
			]);
		}
		Cache::flush();
	}

	public function updateSetting(Request $request, Club $club, string $key): RedirectResponse
	{
		$availableSettings = Setting::getAvailableSettings();
		if (($availableSettings['club'][$key]['adminOnlyEdit'] ?? false) === false) {
			return redirect()->back();
		}
		$request->validate($availableSettings['club'][$key]['validationRules'] ?? []);

		$setting = $club->settings()->updateOrCreate(
			[
				'key' => $key,
				'club_id' => $club->id,
				'feature_id' => $request->get('feature_id'),
			],
			['value' => $request->get('value')]
		);
		Cache::flush();

		if ($key === 'bulb_status') {
			if ($request->get('value') === null) {
				BulbAdapter::where('setting_id', $setting->id)->delete();
			} else {
				BulbAdapter::updateOrCreate(
					[
						'setting_id' => $setting->id,
					],
					[
						'type' => $request->get('provider'),
						'synchronize' => $request->get('synchronize'),
						'credentials' => $request->get('auth'),
					]
				);
			}
		}

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('settings.successfully-updated'),
			]);
	}

	public function addProduct(ProductToClubRequest $request, Club $club, Product $product): RedirectResponse
	{
		$club->products()->attach($product, $request->only(['period', 'cost']));

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Dodano produkt',
			]);
	}

	public function removeProduct(Club $club, int $product_club): RedirectResponse
	{
		$club
			->products()
			->wherePivot('id', $product_club)
			->detach();

		Cache::flush();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto produkt',
			]);
	}

	public function editProduct(
		ProductToClubRequest $request,
		Club $club,
		int $product_club
	): RedirectResponse {
		$club
			->products()
			->wherePivot('id', $product_club)
			->update($request->only(['period', 'cost']));

		Cache::flush();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Edytowano produkt',
			]);
	}

	public function updateGameCommission(
		UpdateGameCommissionRequest $request,
		Club $club,
		int $game_club
	): RedirectResponse {
		$club
			->games()
			->wherePivot('id', $game_club)
			->update($request->only(['include_on_invoice_status', 'include_on_invoice']));

		Cache::flush();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Edytowano gre',
			]);
	}
}
