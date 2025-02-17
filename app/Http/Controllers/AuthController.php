<?php

namespace App\Http\Controllers;

use App\Http\Requests\Widget\SetPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
	public function showRegisterForm(string $secret): Response|RedirectResponse
	{
		$user = User::find(Crypt::decrypt($secret));
		if ($user?->password) {
			return redirect()->route('login');
		}
		app()->setLocale($user->club->country->locale);
		return Inertia::render('Auth/SetPassword', compact('secret'));
	}

	public function register(SetPasswordRequest $request, string $secret): RedirectResponse
	{
		$user = User::where('id', Crypt::decrypt($secret))->firstOrFail();
		$user->password = Hash::make($request->get('password'));
		$user->save();

		Auth::login($user);

		return redirect()->route('dashboard-redirect');
	}
}
