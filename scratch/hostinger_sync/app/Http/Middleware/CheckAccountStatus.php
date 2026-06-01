<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_disabled) {
                $reason = $user->disable_reason ?? 'Your account has been temporarily disabled by the Owner/Super Admin.';

                // For API requests, just return JSON without touching session
                if ($request->expectsJson() || $request->is('api/*')) {
                    Auth::logout();
                    return response()->json(['success' => false, 'message' => $reason], 403);
                }

                // For web requests, handle session
                Auth::logout();
                try {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                } catch (\Exception $e) {
                    // Session not available, skip
                }

                return redirect()->route('login')->withErrors(['email' => $reason]);
            }
        }

        return $next($request);
    }
}
