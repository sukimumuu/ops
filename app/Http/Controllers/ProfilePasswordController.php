<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ProfilePasswordController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                'different:current_password',
                Password::min(12)->mixedCase()->numbers(),
            ],
        ]);

        Auth::logoutOtherDevices($validated['current_password']);

        return $this->storeNewPassword($request, $validated['new_password'], 'Password berhasil diperbarui.');
    }

    private function storeNewPassword(Request $request, string $password, string $message): JsonResponse
    {
        $user = $request->user();
        $user->password = $password;
        $user->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table'))
                ->where('user_id', $user->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => $message,
        ]);
    }
}
