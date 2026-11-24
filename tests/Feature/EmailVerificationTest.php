<?php

use App\Mail\EmailVerificationOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('requires email verification before listing routes', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('listing.create'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('email_verification_required', true);

    $this->actingAs($user)
        ->getJson(route('listing.create'))
        ->assertForbidden()
        ->assertJsonPath('email_verification_required', true);
});

it('sends an otp to a new email and verifies it', function () {
    Mail::fake();
    $user = User::factory()->unverified()->create(['email' => null]);

    $this->actingAs($user)
        ->postJson(route('email-verification.send'), ['email' => 'seller@example.com'])
        ->assertOk()
        ->assertJsonPath('expires_in', 600);

    $mailCode = null;
    Mail::assertSent(EmailVerificationOtpMail::class, function (EmailVerificationOtpMail $mail) use (&$mailCode) {
        $mailCode = $mail->code;

        return $mail->hasTo('seller@example.com');
    });

    $otp = DB::table('email_verification_otps')->where('user_id', $user->id)->first();
    expect($otp)->not->toBeNull();

    expect($mailCode)->not->toBeNull();

    $this->actingAs($user)
        ->postJson(route('email-verification.verify'), ['code' => $mailCode])
        ->assertOk()
        ->assertJsonPath('verified', true);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('limits otp verification attempts', function () {
    $user = User::factory()->unverified()->create();

    DB::table('email_verification_otps')->insert([
        'user_id' => $user->id,
        'email' => $user->email,
        'code_hash' => bcrypt('123456'),
        'expires_at' => now()->addMinutes(10),
        'resend_available_at' => now()->addSeconds(60),
        'attempts' => 4,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->postJson(route('email-verification.verify'), ['code' => '000000'])
        ->assertUnprocessable()
        ->assertJsonPath('attempts_remaining', 0);

    expect(DB::table('email_verification_otps')->where('user_id', $user->id)->exists())->toBeFalse();
});

it('enforces resend cooldown', function () {
    $user = User::factory()->unverified()->create();

    DB::table('email_verification_otps')->insert([
        'user_id' => $user->id,
        'email' => $user->email,
        'code_hash' => bcrypt('123456'),
        'expires_at' => now()->addMinutes(10),
        'resend_available_at' => now()->addSeconds(60),
        'attempts' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->postJson(route('email-verification.send'), ['email' => $user->email])
        ->assertStatus(429)
        ->assertJsonStructure(['retry_after']);
});
