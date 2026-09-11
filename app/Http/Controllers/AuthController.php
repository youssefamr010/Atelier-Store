<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($request->input('email')).'|'.$request->ip());

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $message = "Too many login attempts. Please try again in {$seconds} seconds.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 429);
            }
            return back()->withErrors(['email' => $message])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $user = Auth::user();
            CartController::mergeSessionCartIntoUserCart($user);
            $request->session()->regenerate();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }
            
            return redirect()->intended('/account')->with('success', 'Signed in successfully.');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials. Please try again.'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        return redirect()->route('auth.google')->with('info', 'Customer accounts are created and authenticated exclusively via Google Sign-In.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
        ]);

        // Only update phone if the column exists on the users table
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
            unset($validated['phone']);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'user'    => [
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                ],
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if ($user->password) {
            $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The provided current password does not match our records.');
                    }
                }],
                'password'         => 'required|string|min:8|confirmed',
            ]);
        } else {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        $user->update([
            'password'            => Hash::make($request->input('password')),
            'password_changed_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
        }

        return back()->with('success', 'Password changed successfully.');
    }

    public function destroyAccount(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Admin accounts cannot be deleted via the client portal.']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect('/')->with('success', 'Your account has been deleted successfully.');
    }
}
