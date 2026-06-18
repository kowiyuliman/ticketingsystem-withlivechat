<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Dashboard;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // $request->authenticate();

        // $request->session()->regenerate();

        // $user = auth()->user();

        // if ($user->role == 'admin') {

        //     return redirect('/dashboard');

        // } else {

        //     return redirect('/my-tickets');

        // }

        $request->validate([
        'username' => ['required'],
        'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('username','password'))) {
            return back()->withErrors([
                'username' => 'Username atau password salah',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

            if ($user->role == 'admin') {
                return redirect('/admin/dashboard');
            }

            if ($user->role == 'leader') {
                return redirect('/my-tickets');
            }

            return redirect('/my-tickets'); // user
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
