<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

/**
 * Controller untuk alur autentikasi (login, logout, password, dan Google).
 */
class AuthController extends Controller
{
    /**
     * Display the login view.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|View
     */
    public function tampilHalamanLogin(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'ok']);
        }

        return view('app');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function cekData(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
            ]);
        }

        if ($user && $user->role === 'admin') {
            return redirect()->intended(route('admin.paket.index', absolute: false));
        }

        if ($user && $user->role === 'customer') {
            return redirect()->intended('/');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logout berhasil']);
        }

        return redirect('/');
    }

    /**
     * Update the user's password.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function ubahPassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password diperbarui']);
        }

        return back()->with('status', 'password-updated');
    }

    /**
     * Redirect ke halaman otorisasi Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback Google dan login/registrasi pengguna.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal login dengan Google, silakan coba lagi.',
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Pengguna',
                'password' => Str::random(40),
                'role' => 'customer',
            ]
        );

        // Pastikan nama terbarui jika berubah di Google
        if ($googleUser->getName() && $googleUser->getName() !== $user->name) {
            $user->name = $googleUser->getName();
            $user->save();
        }

        Auth::login($user, remember: true);

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.paket.index', absolute: false));
        }

        if ($user->role === 'customer') {
            return redirect()->intended('/');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
