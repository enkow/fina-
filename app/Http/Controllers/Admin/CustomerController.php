<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExportManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Http\Requests\FilterCustomersRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\GameResource;
use App\Models\Customer;
use App\Models\Game;
use App\Models\Reservation;
use App\Models\TablePreference;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
	private array $reservationsTableData;
	private array $customersTableData;

	// redirect user to default filters if not present
	public function __construct()
	{
		$this->middleware(function (Request $request, Closure $next) {
			if (!request()?->has('filters') || !isset(request()?->get('filters')['reservations']['game'])) {
				return redirect()->route('admin.customers.edit', [
					'customer' => $request->route('customer'),
					'filters' => [
						'reservations' => [
							'game' => Game::first()->id,
						],
					],
				]);
			}

			$this->reservationsTableData = Reservation::tableData(
				gameId: request()?->get('filters')['reservations']['game']
			);

			return $next($request);
		})->only('edit');

		$this->middleware(function (Request $request, Closure $next) {
			$this->customersTableData = Customer::tableData();

			return $next($request);
		})->only('index', 'edit', 'export');
	}

	public function index(): Response
	{
		$customers = CustomerResource::collection(
			Customer::whereHas('club', function ($query) {
				$query->where('country_id', auth()->user()->country_id);
			})
				->with('club')
				->filterable($this->customersTableData['name'], Customer::$availableFilters)
				->sortable($this->customersTableData['name'], Customer::$availableSorters)
				->searchable($this->customersTableData['name'], Customer::$availableSearchers)
				->paginate(50)
				->through(function ($customer) {
					$customer->club_name = $customer->club->name;

					return $customer;
				})
		);

		return Inertia::render('Admin/Customers/Index', [
			'customers' => $customers,
			'customersTableHeadings' => $this->customersTableData['headings'],
		]);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function edit(Customer $customer): Response|RedirectResponse
	{
		$reservations = Reservation::getReservations(
			customerId: $customer->id,
			paginated: true,
			tablePreference: $this->reservationsTableData['preference'],
			tableName: $this->reservationsTableData['name']
		);

		return Inertia::render('Admin/Customers/Edit', [
			'customer' => new CustomerResource($customer),
			'reservations' => $reservations,
			'reservationsTableHeadings' => $this->reservationsTableData['headings'],
			'games' => GameResource::collection(Game::all()),
		]);
	}

	public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
	{
		$customer->update($request->only(['first_name', 'last_name', 'email', 'phone']));

		return redirect()
			->route('admin.customers.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano dane klienta',
			]);
	}

	public function export(FilterCustomersRequest $request): \Illuminate\Http\Response|BinaryFileResponse
	{
		$customers = Customer::whereHas('club', static function ($query) {
			$query->where('country_id', auth()->user()->country_id);
		})
			->with('club')
			->filterable($this->customersTableData['name'], Customer::$availableFilters)
			->sortable($this->customersTableData['name'], Customer::$availableSorters)
			->searchable($this->customersTableData['name'], Customer::$availableSearchers)
			->get()
			->map(function ($customer) {
				$customer->club_name = $customer->club->name;

				return TablePreference::getDataArrayFromModel(
					$customer,
					$this->customersTableData['preference']
				);
			});

		return ExportManager::export(
			TablePreference::getEnabledColumns($this->customersTableData['preference']),
			$this->customersTableData['headings'],
			$customers,
			$request->get('extension'),
			['a4', 'landscape']
		);
	}

	public function destroy(Customer $customer): RedirectResponse
	{
		$customer->delete();

		return redirect()
			->route('admin.customers.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto klienta',
			]);
	}
}
