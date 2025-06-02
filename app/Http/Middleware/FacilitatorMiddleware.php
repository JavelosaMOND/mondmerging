<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FacilitatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // If not logged in, redirect to login page
            return redirect()->route('login');
        }

        // Check both user_type and role fields for backward compatibility
        $userRole = Auth::user()->user_type ?? Auth::user()->role;

        if ($userRole !== 'facilitator') {
            // If user is logged in but not a facilitator, redirect to their appropriate dashboard
            if ($userRole === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($userRole === 'barangay') {
                return redirect()->route('barangay.dashboard');
            }

            // Default redirect to login
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
