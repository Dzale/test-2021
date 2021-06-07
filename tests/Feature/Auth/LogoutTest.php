<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    /** @test */
    public function endpoint_shouldReturn_200()
    {
        factory(User::class)->create(['email' => 'unique@email.com', 'password' => bcrypt('password')]);

        $response = $this->getJson(
            '/sanctum/csrf-cookie'
        );

        $response->assertCookie('XSRF-TOKEN');

        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => 'unique@email.com',
                'password' => 'password'
            ]
        );

        $this->assertAuthenticated('web');

        $response = $this->postJson(
            '/api/auth/logout',
            []
        );

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => null,
            'data' => null
        ]);
        $this->assertGuest('web');
    }

    /** @test */
    public function endpoint_withoutCsrfCookie_shouldReturn_401()
    {
        $response = $this->postJson(
            '/api/auth/logout',
            []
        );

        $response->assertResponseError('Unauthenticated.', 401);
        $this->assertGuest('web');
    }
}
