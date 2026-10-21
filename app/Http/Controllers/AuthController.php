<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(string $audience = 'student'): View
    {
        return view('auth.login', ['audience' => $audience]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $audience = $request->routeIs('counselor.login.store') ? 'counselor' : 'student';
        $authenticated = Auth::attempt($credentials, $request->boolean('remember'));

        if (! $authenticated) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $roleAllowed = $audience === 'counselor'
            ? $request->user()->hasRole(User::ROLE_COUNSELOR, User::ROLE_ADMIN)
            : $request->user()->hasRole(User::ROLE_USER);

        if (! $roleAllowed) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account cannot sign in through this portal.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $destination = $audience === 'counselor'
            ? route('assessments.index')
            : route('student.assessments.index');

        return redirect()->intended($destination);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = \App\Models\User::create($validated);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.assessments.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}