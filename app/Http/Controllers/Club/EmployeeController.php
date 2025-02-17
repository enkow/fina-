<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessEmployeeRequest;
use App\Http\Requests\Club\StoreEmployeeRequest;
use App\Http\Requests\Club\UpdateEmployeeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
	public function index(): Response
	{
		$employees = UserResource::collection(
			club()
				->users()
				->where('first_name', '!=', 'Administrator')
				->orderByDesc('last_login')
				->paginate(request()['perPage']['employees'] ?? 10)
		);

		return Inertia::render('Club/Employees/Index', compact(['employees']));
	}

	public function store(StoreEmployeeRequest $request): RedirectResponse
	{
		club()
			->users()
			->create($request->only(['type', 'first_name', 'last_name', 'email']));

		return redirect()
			->route('club.employees.index')
			->with('message', [
				'type' => 'success',
				'content' => __('employee.successfully-stored'),
			]);
	}

	public function create(): Response
	{
		return Inertia::render('Club/Employees/Create');
	}

	public function edit(AccessEmployeeRequest $request, User $employee): Response
	{
		return Inertia::render('Club/Employees/Edit', [
			'employee' => new UserResource($employee),
		]);
	}

	public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
	{
		$employee->update($request->only(['type', 'first_name', 'last_name', 'email']));

		return redirect()
			->route('club.employees.index')
			->with('message', [
				'type' => 'info',
				'content' => __('employee.successfully-updated'),
			]);
	}

	public function destroy(AccessEmployeeRequest $request, User $employee): RedirectResponse
	{
		$employee->delete();

		return redirect()
			->route('club.employees.index')
			->with('message', [
				'type' => 'info',
				'content' => __('employee.successfully-destroyed'),
			]);
	}
}
