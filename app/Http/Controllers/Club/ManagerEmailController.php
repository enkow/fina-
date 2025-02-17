<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessManagerEmailRequest;
use App\Http\Requests\Club\StoreManagerEmailRequest;
use App\Models\ManagerEmail;
use Illuminate\Http\RedirectResponse;

class ManagerEmailController extends Controller
{
	public function store(StoreManagerEmailRequest $request): RedirectResponse
	{
		club()
			->managerEmails()
			->create([
				'game_id' => $request->get('game_id'),
				'email' => $request->get('email'),
			]);

		return redirect()->back();
	}

	public function destroy(AccessManagerEmailRequest $request, ManagerEmail $managerMail): RedirectResponse
	{
		$managerMail->delete();

		return redirect()->back();
	}
}
