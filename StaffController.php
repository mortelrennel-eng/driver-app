<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'super_admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Super Admin access required.'], 403);
            }
            abort(403, 'Access denied. This area is restricted to the System Owner.');
        }

        return $next($request);
    }
}
