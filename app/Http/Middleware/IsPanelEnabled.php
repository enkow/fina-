<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IsPanelEnabled
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request                                        $request
	 * @param  Closure(Request): (Response|RedirectResponse)  $next
	 *
	 * @return Response|RedirectResponse|JsonResponse
	 */
	public function handle(
		Request $request,
		Closure $next
	): Response|RedirectResponse|JsonResponse|BinaryFileResponse|StreamedResponse {
		if (!club()->panel_enabled && session()->get('adminId', null) === null) {
			Auth::logout();

			return redirect()
				->route('login')
				->withErrors(['message' => __('auth.panel-disabled')]);
		}

		return $next($request);
	}
}
