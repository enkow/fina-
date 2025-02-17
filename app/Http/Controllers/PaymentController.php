<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
	public function initialize(string $hash): RedirectResponse
	{
		$reservation = Reservation::withHash()->firstOrFail();
		return redirect()->route('login');
	}

	public function receiveNotification(): bool
	{
		return true;
	}
}
