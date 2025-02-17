<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessAgreementRequest;
use App\Http\Requests\Club\StoreAgreementRequest;
use App\Http\Requests\Club\UpdateAgreementRequest;
use App\Http\Resources\AgreementResource;
use App\Models\Agreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AgreementController extends Controller
{
	public function index(): Response
	{
		$agreements = AgreementResource::collection(
			club()
				->agreements()
				->with('club')
				->paginate(10)
		);

		return Inertia::render('Club/Agreements/Index', [
			'agreements' => $agreements,
		]);
	}

	public function edit(AccessAgreementRequest $request, Agreement $agreement): Response
	{
		return Inertia::render('Club/Agreements/Edit', [
			'agreement' => new AgreementResource($agreement),
		]);
	}

	public function destroy(AccessAgreementRequest $request, Agreement $agreement): RedirectResponse
	{
		if ($agreement->file) {
			Storage::disk('clubAgreements')->delete($agreement->file);
		}
		$agreement->update([
			'file' => null,
			'text' => null,
			'active' => false,
		]);

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('agreement.successfully-destroyed'),
			]);
	}

	public function update(UpdateAgreementRequest $request, Agreement $agreement): RedirectResponse
	{
		$updateArray = $request->only(['content_type', 'text', 'active', 'required']);
		$contentChangedStatus =
			$request->get('text') !== $agreement->text ||
			$request->get('content_type') !== $agreement->content_type->value;
		if ($request->file('file') !== null) {
			$contentChangedStatus = true;
			if ($agreement->file) {
				Storage::disk('clubAgreements')->delete($agreement->file);
			}
			$path = $request->file('file')->storePublicly('/', ['disk' => 'clubAgreements']);
			$updateArray['file'] = $path;
		}
		$agreement->update($updateArray);

		//delete customer consents if content has been changed
		if ($contentChangedStatus) {
			$agreement->customers()->detach();
		}

		return redirect()
			->route('club.agreements.index')
			->with('message', [
				'type' => 'info',
				'content' => __('agreement.successfully-updated'),
			]);
	}

	public function toggleActive(AccessAgreementRequest $request, Agreement $agreement): RedirectResponse
	{
		$agreement->update([
			'active' => !$agreement->active,
		]);

		return redirect()
			->route('club.agreements.index')
			->with('message', [
				'type' => 'info',
				'content' => __('agreement.successfully-updated'),
			]);
	}

	public function toggleRequired(AccessAgreementRequest $request, Agreement $agreement): RedirectResponse
	{
		$agreement->update([
			'required' => !$agreement->required,
		]);

		return redirect()
			->route('club.agreements.index')
			->with('message', [
				'type' => 'info',
				'content' => __('agreement.successfully-updated'),
			]);
	}
}
