<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->role !== $role) {
            // Optional: you can redirect to their respective dashboard
            // if (Auth::user()->role === 'admin') return redirect('/admin');
            // if (Auth::user()->role === 'advokat') return redirect('/advokat');
            // if (Auth::user()->role === 'klien') return redirect('/klien');
            
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
