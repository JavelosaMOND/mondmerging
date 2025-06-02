<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Redirect based on user role or user_type
            $userRole = $user->user_type ?? $user->role; // Use user_type if available, otherwise fall back to role

            return match ($userRole) {
                'admin' => redirect()->route('admin.dashboard'),
                'cluster' => redirect()->route('cluster.index'),
                'facilitator' => redirect()->route('facilitator.dashboard'),
                'barangay' => redirect()->route('barangay.dashboard'),
                default => redirect()->route('login')->with('error', 'Unauthorized'),
            };
        }

        return back()->withErrors(['email' => 'Invalid login credentials']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
