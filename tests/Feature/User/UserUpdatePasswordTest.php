<?php

namespace Tests\Feature\User;

use App\Models\User;
use Hash;
use Tests\TestCase;

class UserUpdatePasswordTest extends TestCase
{
    /** @test */
    public function endpoint_shouldUpdatePassword()
    {
        $user = factory(User::class)->create([
            'email' => 'test@test.com',
            'password' => bcrypt('123456')
        ]);

        $this->actingAs($user);

        \Gate::before(function () {
            return true;
        }); //no need to test authorizations here, this is handled via different test

        $response = $this->postJson(
            "/api/users/me/password",
            [
                'password' => '123456',
                'new_password' => '654321',
                'new_password_confirmation' => '654321',
            ]
        );

        $user->refresh();

        $response->assertSuccessful();
        $this->assertFalse(Hash::check('123456', $user->password));
        $this->assertTrue(Hash::check('654321', $user->password));
    }

    /** @test */
    public function endpoint_withoutData_shouldReturn_422()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $response = $this->postJson(
            "/api/users/me/password",
            []
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['password' => 'The password field is required.']);
        $response->assertJsonValidationErrors(['new_password' => 'The new password field is required.']);
    }

    /** @test */
    public function endpoint_withInvalidCurrentPassword_shouldReturn_422()
    {
        $user = factory(User::class)->create([
            'email' => 'test@test.com',
            'password' => bcrypt('123456')
        ]);

        $this->actingAs($user);

        $response = $this->postJson(
            "/api/users/me/password",
            [
                'password' => '1234567',
                'new_password' => '654321',
                'new_password_confirmation' => '654321',
            ]
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['password' => 'Password you entered is invalid!']);
    }

    /** @test */
    public function endpoint_withoutNewPasswordConfirmation_shouldReturn_422()
    {
        $user = factory(User::class)->create([
            'email' => 'test@test.com',
            'password' => bcrypt('123456')
        ]);

        $this->actingAs($user);

        $response = $this->postJson(
            "/api/users/me/password",
            [
                'password' => '123456',
                'new_password' => '654321',
            ]
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['new_password' => 'The new password confirmation does not match.']);
    }

    /** @test */
    public function endpoint_withInvalidNewPasswordConfirmation_shouldReturn_422()
    {
        $user = factory(User::class)->create([
            'email' => 'test@test.com',
            'password' => bcrypt('123456')
        ]);

        $this->actingAs($user);

        $response = $this->postJson(
            "/api/users/me/password",
            [
                'password' => '123456',
                'new_password' => '654321',
                'new_password_confirmation' => '7654321',
            ]
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['new_password' => 'The new password confirmation does not match.']);
    }

    /** @test */
    public function endpoint_withoutAuth_shouldReturn_401()
    {
        $response = $this->postJson(
            "/api/users/me/password"
        );

        $response->assertResponseError('Unauthenticated.', 401);
    }
}
