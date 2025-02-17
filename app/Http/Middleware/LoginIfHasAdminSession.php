<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoginIfHasAdminSession
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request                                        $request
	 * @param  Closure(Request): (Response|RedirectResponse)  $next
	 *
	 * @return Response|RedirectResponse
	 */
	public function handle(Request $request, Closure $next)
	{
		if (session()->has('adminId')) {
			$adminId = session()->get('adminId');
			session()->forget('adminId');
			$admin = User::find($adminId);
			Auth::login($admin);

			return redirect()->route('admin.countries.index');
		}

		return $next($request);
	}
}
