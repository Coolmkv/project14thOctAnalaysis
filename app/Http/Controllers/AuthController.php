<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

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

        $audience = match (true) {
            $request->routeIs('admin.login.store') => 'admin',
            $request->routeIs('group-admin.login.store') => 'group_admin',
            $request->routeIs('counselor.login.store') => 'counselor',
            default => 'student',
        };
        $authenticated = Auth::attempt($credentials, $request->boolean('remember'));

        if (! $authenticated) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $roleAllowed = match ($audience) {
            'admin' => $request->user()->hasRole(User::ROLE_ADMIN),
            'group_admin' => $request->user()->hasRole(User::ROLE_GROUP_ADMIN),
            'counselor' => $request->user()->hasRole(User::ROLE_COUNSELOR),
            default => $request->user()->hasRole(User::ROLE_USER),
        };

        if (! $roleAllowed) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account cannot sign in through this portal.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->user()->must_change_password) {
            return redirect()->route('account.password.edit');
        }

        $destination = match ($audience) {
            'admin' => route('admin.dashboard'),
            'group_admin' => route('group-admin.dashboard'),
            'counselor' => route('assessments.index'),
            default => route('student.assessments.index'),
        };

        return redirect()->intended($destination);
    }

    public function showChangePassword(): View
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $request->user()->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        $destination = match ($request->user()->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_GROUP_ADMIN => route('group-admin.dashboard'),
            User::ROLE_COUNSELOR => route('assessments.index'),
            default => route('student.assessments.index'),
        };

        return redirect()->to($destination)
            ->with('status', 'Your password has been updated.');
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

        $user = User::create($validated);
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
