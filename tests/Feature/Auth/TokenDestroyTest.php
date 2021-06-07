<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class TokenDestroyTest extends TestCase
{
    /** Sanctum bug */
    public function endpoint_shouldReturn_200()
    {
        factory(User::class)->create([
            'email' => 'unique@email.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson(
            '/api/auth/tokens',
            [
                'email' => 'unique@email.com',
                'password' => 'password',
                'device_name' => 'ios'
            ]
        ); // Issuing a token, expecting 201

        $response->assertStatus(201);

        $token = $response->getData()->data->token;

        $response = $this->deleteJson(
            '/api/auth/tokens',
            [],
            [
                'Authorization' => "Bearer $token"
            ]
        );

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => null,
            'data' => null
        ]);
    }

    /** @test */
    public function endpoint_withoutToken_shouldReturn_401()
    {
        $response = $this->deleteJson(
            '/api/auth/tokens',
            []
        );

        $response->assertResponseError('Unauthenticated.', 401);
    }
}
