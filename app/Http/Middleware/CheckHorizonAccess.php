<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckHorizonAccess
{
	public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
	{
		if (auth()->check() && auth()->user()->type === 'admin') {
			return $next($request);
		}

		abort(403);
	}
}
