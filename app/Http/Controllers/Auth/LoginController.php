<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check honeypot field (anti-bot protection)
        if ($request->filled('website')) {
            // Bot detected, return generic error
            throw ValidationException::withMessages([
                'username' => __('بيانات الدخول غير صحيحة.'),
            ]);
        }

        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials = $this->getCredentials($request);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        // Add small delay to prevent brute force attacks
        sleep(1);

        throw ValidationException::withMessages([
            'username' => __('بيانات الدخول غير صحيحة.'),
        ]);
    }

    /**
     * الحصول على CSRF token جديد (للتحديث التلقائي)
     */
    public function getCSRFToken(Request $request)
    {
        return response()->json([
            'token' => csrf_token(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function getCredentials(Request $request): array
    {
        $username = $request->input('username');

        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $username,
            'password' => $request->input('password'),
        ];
    }
}
