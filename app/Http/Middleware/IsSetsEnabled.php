<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IsSetsEnabled
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request                                        $request
	 * @param  Closure(Request): (Response|RedirectResponse)  $next
	 *
	 * @return Response|RedirectResponse
	 */
	public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
	{
		if (!club() || !club()->sets_enabled) {
			return redirect()->route('index');
		}

		return $next($request);
	}
}
