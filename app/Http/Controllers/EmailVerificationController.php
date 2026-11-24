<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationOtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EmailVerificationController extends Controller
{
    private const OTP_ATTEMPT_LIMIT = 5;

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'verified' => $user->email_verified_at !== null,
            'email' => $user->email,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $email = strtolower(trim($validated['email']));

        $existing = DB::table('email_verification_otps')
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null
            && now()->lt($existing->resend_available_at)) {
            return response()->json([
                'message' => 'Kode baru belum dapat dikirim. Tunggu hingga cooldown selesai.',
                'retry_after' => max(0, now()->diffInSeconds($existing->resend_available_at, false)),
            ], 429);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('email_verification_otps')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'email' => $email,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
                'resend_available_at' => now()->addSeconds(60),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $user->forceFill([
            'email' => $email,
            'email_verified_at' => null,
        ])->save();

        Mail::to($email)->send(new EmailVerificationOtpMail($code));

        return response()->json([
            'message' => 'Kode OTP 6 digit telah dikirim.',
            'expires_in' => 600,
            'resend_in' => 60,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $otp = DB::table('email_verification_otps')
            ->where('user_id', $user->id)
            ->first();

        if ($otp === null) {
            return response()->json([
                'message' => 'Kode OTP belum dikirim atau sudah tidak berlaku.',
            ], 422);
        }

        if (now()->gt($otp->expires_at)) {
            DB::table('email_verification_otps')->where('user_id', $user->id)->delete();

            return response()->json([
                'message' => 'Kode OTP telah kedaluwarsa. Kirim ulang kode baru.',
            ], 422);
        }

        if ($otp->attempts >= self::OTP_ATTEMPT_LIMIT) {
            DB::table('email_verification_otps')->where('user_id', $user->id)->delete();

            return response()->json([
                'message' => 'Percobaan OTP telah habis. Kirim ulang kode baru.',
            ], 422);
        }

        if (! Hash::check($validated['code'], $otp->code_hash)) {
            $attempts = $otp->attempts + 1;
            if ($attempts >= self::OTP_ATTEMPT_LIMIT) {
                DB::table('email_verification_otps')->where('user_id', $user->id)->delete();

                return response()->json([
                    'message' => 'Percobaan OTP telah habis. Kirim ulang kode baru.',
                    'attempts_remaining' => 0,
                ], 422);
            }

            DB::table('email_verification_otps')
                ->where('user_id', $user->id)
                ->update([
                    'attempts' => $attempts,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'message' => 'Kode OTP tidak valid.',
                'attempts_remaining' => max(0, self::OTP_ATTEMPT_LIMIT - $attempts),
            ], 422);
        }

        if ($otp->email !== $user->email) {
            return response()->json([
                'message' => 'Email telah berubah. Kirim ulang kode OTP.',
            ], 422);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();
        DB::table('email_verification_otps')->where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Email berhasil diverifikasi.',
            'verified' => true,
        ]);
    }
}
