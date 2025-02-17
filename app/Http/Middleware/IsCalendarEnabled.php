<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;

class IsCalendarEnabled
{
	public function handle(Request $request, Closure $next)
	{
		return $next($request);
	}
}
