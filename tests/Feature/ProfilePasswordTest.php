<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('allows an authenticated user to change their password', function () {
    $user = User::factory()->create([
        'password' => 'OldPassword123',
    ]);

    $response = $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewSecurePassword456',
            'new_password_confirmation' => 'NewSecurePassword456',
        ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Password berhasil diperbarui.');

    expect(Hash::check('NewSecurePassword456', $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('OldPassword123', $user->fresh()->password))->toBeFalse();
});

it('rejects a password change when the current password is incorrect', function () {
    $user = User::factory()->create([
        'password' => 'OldPassword123',
    ]);

    $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'WrongPassword123',
            'new_password' => 'NewSecurePassword456',
            'new_password_confirmation' => 'NewSecurePassword456',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');

    expect(Hash::check('OldPassword123', $user->fresh()->password))->toBeTrue();
});

it('rejects weak, reused, or mismatched new passwords', function (array $passwordData) {
    $user = User::factory()->create([
        'password' => 'OldPassword123',
    ]);

    $this->actingAs($user)
        ->putJson(route('profile.password.update'), [
            'current_password' => 'OldPassword123',
            ...$passwordData,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('new_password');

    expect(Hash::check('OldPassword123', $user->fresh()->password))->toBeTrue();
})->with([
    'weak password' => [[
        'new_password' => 'short',
        'new_password_confirmation' => 'short',
    ]],
    'reused password' => [[
        'new_password' => 'OldPassword123',
        'new_password_confirmation' => 'OldPassword123',
    ]],
    'mismatched confirmation' => [[
        'new_password' => 'NewSecurePassword456',
        'new_password_confirmation' => 'DifferentPassword456',
    ]],
]);

it('requires authentication to change a password', function () {
    $this->putJson(route('profile.password.update'), [
        'current_password' => 'OldPassword123',
        'new_password' => 'NewSecurePassword456',
        'new_password_confirmation' => 'NewSecurePassword456',
    ])->assertUnauthorized();
});
