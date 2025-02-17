<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IsUserType
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request                                        $request
	 * @param  Closure(Request): (Response|RedirectResponse)  $next
	 *
	 * @return Response|RedirectResponse
	 */
	public function handle(
		Request $request,
		Closure $next,
		...$roles
	): Response|RedirectResponse|JsonResponse|BinaryFileResponse|StreamedResponse {
		if (!in_array(auth()->user()?->type, $roles, true)) {
			return redirect()->route('login');
		}

		return $next($request);
	}
}
