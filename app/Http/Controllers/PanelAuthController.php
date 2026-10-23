<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PanelAuthController extends Controller
{
    /**
     * Roles allowed to sign in to the administrator panel.
     *
     * @var list<string>
     */
    private const PANEL_ROLES = ['Superadmin', 'Admin', 'PPAT'];

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null && $user->hasAnyRole(self::PANEL_ROLES)) {
            return redirect()->route($this->homeRouteFor($user));
        }

        return view('auth.panel-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole(self::PANEL_ROLES)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses ke panel administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->homeRouteFor($user)));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('panel.login');
    }

    private function homeRouteFor(User $user): string
    {
        return $user->hasRole('PPAT') ? 'land-deed.index' : 'dashboard';
    }
}
