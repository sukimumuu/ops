<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedForSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null
            && $user->email_verified_at === null
            && ! $request->routeIs('email-verification.*')
            && ! $request->routeIs('seller.listing')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Verifikasi email diperlukan untuk mengakses fitur penjual.',
                    'email_verification_required' => true,
                ], 403);
            }

            $request->session()->flash('email_verification_required', true);
            $request->session()->flash('email_verification_intended_url', $request->fullUrl());

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
