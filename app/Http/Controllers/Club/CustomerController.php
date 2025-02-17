<?php

namespace App\Http\Controllers\Club;

use App\Enums\AgreementType;
use App\Exports\ExportManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReservationsRequest;
use App\Http\Requests\Club\AccessCustomerRequest;
use App\Http\Requests\Club\StoreCustomerRequest;
use App\Http\Requests\Club\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\TagResource;
use App\Models\Agreement;
use App\Models\Customer;
use App\Models\Game;
use App\Models\Reservation;
use App\Models\TablePreference;
use App\Models\Tag;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;
use Laravel\Octane\Exceptions\DdException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
	private array $reservationsTableData;
	private array $customersTableData;

	public function __construct()
	{
		$this->middleware(function (Request $request, Closure $next) {
			if (
				!request()?->has('filters') ||
				!isset(
					request()?->get('filters')['reservations'],
					request()?->get('filters')['reservations']['game']
				)
			) {
				return redirect()->route('club.customers.show', [
					'customer' => $request->route('customer'),
					'filters[reservations][game]' => Game::first()->id ?? 1,
				]);
			}
			$this->reservationsTableData = Reservation::tableData(
				gameId: request()?->get('filters')['reservations']['game']
			);

			return $next($request);
		})->only('show', 'exportReservations');

		$this->middleware(function (Request $request, Closure $next) {
			$this->customersTableData = Customer::tableData();

			return $next($request);
		})->only('index', 'export');
	}

	public function index(): Response
	{
		$customers = CustomerResource::collection(
			club()
				->customers()
				->groupBy('customers.id')
				->filterable($this->customersTableData['name'], Customer::$availableFilters)
				->sortable($this->customersTableData['name'], Customer::$availableSorters)
				->searchable($this->customersTableData['name'], Customer::$availableSearchers)
				->with('tags', 'club', 'agreements')
				->withMax('reservations', 'created_at')
				->withCount('reservations')
				->paginate(request()['perPage']['customers'] ?? 10)
		);

		return Inertia::render('Club/Customers/Index', [
			'customersTableHeadings' => $this->customersTableData['headings'],
			'customers' => $customers,
		]);
	}

	/**
	 * @throws NotFoundExceptionInterface
	 * @throws DdException
	 * @throws ContainerExceptionInterface
	 * @throws JsonException
	 */
	public function store(StoreCustomerRequest $request): RedirectResponse
	{
		$customer = club()
			->customers()
			->create(
				array_merge($request->only(['email', 'first_name', 'last_name', 'phone']), [
					'locale' => club()->country->locale ?? 'en',
					'verified' => true,
				])
			);
		$tagNames = array_unique(
			array_column(
				json_decode(
					request()->get('tags') ?? json_encode([], JSON_THROW_ON_ERROR),
					true,
					512,
					JSON_THROW_ON_ERROR
				) ?? [],
				'value'
			)
		);
		foreach ($tagNames as $tagName) {
			$customer->tags()->attach(Tag::firstOrCreate(['name' => $tagName]));
		}

		foreach ($request->all()['agreements'] as $agreementType => $status) {
			if (!$status) {
				continue;
			}
			$agreement = club()
				->agreements()
				->where('type', $agreementType)
				->first();
			$customer->agreements()->attach($agreement);
		}

		return redirect()
			->route('club.customers.index')
			->with('message', [
				'type' => 'success',
				'content' => __('customer.successfully-stored'),
			]);
	}

	public function create(): Response
	{
		$agreements = club()
			->agreements()
			->where('active', true)
			->get();
		$availableTags = TagResource::collection(
			club()
				->tags()
				->get()
		);
		return Inertia::render('Club/Customers/Create', compact('agreements', 'availableTags'));
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function show(AccessCustomerRequest $request, Customer $customer): Response|RedirectResponse
	{
		$reservations = Reservation::getReservations(
			clubId: clubId(),
			customerId: $customer->id,
			paginated: true,
			itemsPerPage: request()['perPage']['reservations'] ?? 10,
			paginationTableName: $this->reservationsTableData['name'],
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $this->reservationsTableData['name']
		);

		$latestReservation = $customer
			->reservations()
			->orderByDesc('created_at')
			->first();

		return Inertia::render('Club/Customers/Show', [
            'games' => GameResource::collection(
                club()->games()->with('features')->get()
            ),
			'club' => club()->getCalendarResource(),
			'reservationTableHeadings' => $this->reservationsTableData['headings'],
			'reservations' => $reservations,
			'customer' => new CustomerResource($customer->load('tags')),
			'availableTags' => TagResource::collection(
				club()
					->tags()
					->whereNotIn('tags.id', $customer->tags->pluck('id'))
					->get()
			),
			'latestReservation' => $latestReservation ? new ReservationResource($latestReservation) : null,
			'reservationsCount' => $customer->reservations()->count(),
			'reservationsPriceSum' => (int) $customer->reservations()->sum('price'),
		]);
	}

	public function search(): JsonResponse
	{
		return response()->json(
			strlen(request()->get('search', '')) > 0
				? CustomerResource::collection(
					Customer::where(function ($query) {
						$query
							->where('phone', 'like', '%' . request()->get('search') . '%')
							->orWhere('last_name', 'like', '%' . request()->get('search') . '%')
							->orWhere('email', 'like', '%' . request()->get('search') . '%');
					})
						->with('tags')
						->where('club_id', clubId())
						->take(5)
						->get()
				)
				: []
		);
	}

	public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
	{
		$updateArray = $request->only(['email', 'first_name', 'last_name', 'phone']);
		if ($customer->password) {
			unset($updateArray['email']);
		}

		$customer->update($updateArray);

		return redirect()
			->route('club.customers.index')
			->with('message', [
				'type' => 'info',
				'content' => __('customer.successfully-updated'),
			]);
	}

	public function toggleConsent(Customer $customer, string $agreementType): RedirectResponse
	{
		$agreement = club()
			->agreements()
			->where('type', $agreementType)
			->first();
		if (empty($agreement)) {
			return redirect()->back();
		}
		$customerConsentStatus = $customer
			->agreements()
			->where('agreements.id', $agreement->id)
			->first();
		if (!empty($customerConsentStatus)) {
			$customer->agreements()->detach($agreement);
		} else {
			$customer->agreements()->attach($agreement);
		}

		return redirect()->back();
	}

	public function destroy(AccessCustomerRequest $request, Customer $customer): RedirectResponse
	{
		$customer->delete();

		return redirect()
			->route('club.customers.index')
			->with('message', [
				'type' => 'info',
				'content' => __('customer.successfully-destroyed'),
			]);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function exportReservations(
		FilterReservationsRequest $request,
		Customer $customer
	): \Illuminate\Http\Response|BinaryFileResponse {
		$reservations = Reservation::getReservations(
			clubId: clubId(),
			customerId: $customer->id,
			paginated: false,
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $this->reservationsTableData['name']
		)->map(function ($reservation) {
			return Reservation::prepareOutputForExport($reservation);
		});

		return ExportManager::export(
			TablePreference::getEnabledColumns(
				$this->reservationsTableData['preference'],
				Reservation::$exportFieldExclusions,
				Reservation::$exportFieldInclusions
			),
			$this->reservationsTableData['headings'],
			$reservations,
			$request->get('extension')
		);
	}

	public function export(FilterReservationsRequest $request): \Illuminate\Http\Response|BinaryFileResponse
	{
		$customers = club()
			->customers()
			->filterable($this->customersTableData['name'], Customer::$availableFilters)
			->sortable($this->customersTableData['name'], Customer::$availableSorters)
			->searchable($this->customersTableData['name'], Customer::$availableSearchers)
			->with('latestReservation', 'tags', 'agreements')
			->withCount('reservations')
			->get()
			->map(function ($customer) {
				$customer['tags'] = implode(', ', array_column($customer->tags->toArray(), 'name'));
				$customer->latest_reservation = $customer->latestReservation->created_at ?? __('main.none');
				$customer->full_name = $customer->fullName();
				$customer->agreement_0 = __(
					'main.' .
						(count($customer->agreements->where('type', AgreementType::GeneralTerms))
							? 'yes'
							: 'no')
				);
				$customer->agreement_1 = __(
					'main.' .
						(count($customer->agreements->where('type', AgreementType::PrivacyPolicy))
							? 'yes'
							: 'no')
				);
				$customer->agreement_2 = __(
					'main.' .
						(count($customer->agreements->where('type', AgreementType::MarketingAgreement))
							? 'yes'
							: 'no')
				);

				return TablePreference::getDataArrayFromModel(
					$customer,
					$this->customersTableData['preference']
				);
			});

		return ExportManager::export(
			TablePreference::getEnabledColumns($this->customersTableData['preference']),
			Customer::tableHeadingTranslations(true),
			$customers,
			$request->get('extension'),
			['a4', 'landscape']
		);
	}

	public function detachTag(Customer $customer, Tag $tag): RedirectResponse
	{
		$customer->tags()->detach($tag);

		return redirect()->back();
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws JsonException
	 */
	public function updateTags(Customer $customer): RedirectResponse
	{
		$customer->syncTags(
			array_unique(
				array_column(
					json_decode(request()->get('tags'), true, 512, JSON_THROW_ON_ERROR) ?? [],
					'value'
				)
			)
		);
		return redirect()->back();
	}

	public function syncAgreementStatus(
		Customer $customer,
		Agreement $agreement,
		bool $status
	): RedirectResponse {
		if ($agreement->club_id !== clubId()) {
			return redirect()->back();
		}
		$method = $status ? 'attach' : 'detach';
		$customer->agreements()->{$method}($agreement);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('customer.agreement-status-successfully-synced'),
			]);
	}
}
