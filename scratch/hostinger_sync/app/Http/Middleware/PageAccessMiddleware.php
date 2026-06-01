<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PageAccessMiddleware
{
    /**
     * Routes/patterns that are ALWAYS accessible (never restricted).
     */
    protected array $alwaysAllowed = [
        'login',
        'logout',
        'register',
        'my-account',
        'my-account.update-profile',
        'my-account.update-profile-image',
        'my-account.change-password',
        'my-account.forgot-password',
        'notifications.dismiss',
        'super-admin.*',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Super admins bypass all page restrictions
        if (!$user || $user->role === 'super_admin') {
            return $next($request);
        }

        $allowedPages = $user->allowed_pages ?? [];

        // Decode if it's a string
        if (is_string($allowedPages)) {
            $allowedPages = json_decode($allowedPages, true) ?? [];
        }

        // We use null or [] to mean "no pages allowed" (blocked)
        // If allowed_pages is null or [], access is denied by default for non-super-admins.

        $routeName = $request->route()?->getName() ?? '';

        // Check always-allowed routes
        foreach ($this->alwaysAllowed as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return $next($request);
            }
        }

        // Check if current route is in the user's allowed pages
        foreach ($allowedPages as $allowed) {
            if (Str::is($allowed, $routeName)) {
                return $next($request);
            }
        }

        // Block access
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Access to this page has been restricted by the system owner.'], 403);
        }

        // Determine a safe fallback route if they are blocked
        $fallbackRoute = 'my-account';
        
        // If the current route IS the fallback route (shouldn't happen due to alwaysAllowed, but just in case)
        if ($routeName === $fallbackRoute) {
            abort(403, 'Access denied by system owner.');
        }

        return redirect()->route($fallbackRoute)->with(
            'error',
            'You do not have permission to access that page. Contact the system owner.'
        );
    }
}
