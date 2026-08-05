<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // Flash a message for better user experience
            if ($request->session()) {
                $request->session()->flash('info', 'Please log in to access this page.');
            }
            
            // Clear any existing session data
            if ($request->session()) {
                $request->session()->flush();
            }
            
            return route('login');
        }
    }

    /**
     * Handle an unauthenticated request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
            
            // If authenticated and trying to access login/register, redirect to dashboard
            if ($this->auth->check() && $this->isAuthRoute($request)) {
                return redirect()->route('dashboard');
            }
            
            return $next($request);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Clear session on authentication failure
            if ($request->session()) {
                $request->session()->flush();
                $request->session()->flash('info', 'Your session has expired. Please log in again.');
            }
            
            return $this->unauthenticated($request, $guards);
        }
    }

    /**
     * Check if the current route is an authentication route
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isAuthRoute($request)
    {
        $authRoutes = ['login', 'login.submit', 'register', 'register.submit'];
        return in_array($request->route()->getName(), $authRoutes);
    }
}
