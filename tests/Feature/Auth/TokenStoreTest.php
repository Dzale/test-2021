<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Mockery\MockInterface;
use Tests\TestCase;

class TokenStoreTest extends TestCase
{
    /** @test */
    public function endpoint_shouldReturn_201()
    {
        factory(User::class)->create([
            'email' => 'unique@email.com',
            'password' => 'password'
        ]);

        $this->mock(Factory::class, function (MockInterface $mock) {
            $mock->expects('attempt')->with([
                'email' => 'unique@email.com',
                'password' => 'password'
            ])->andReturn(true);
        });

        $response = $this->postJson(
            '/api/auth/tokens',
            [
                'email' => 'unique@email.com',
                'password' => 'password',
                'device_name' => 'ios'
            ]
        );

        $response->assertStatus(201);
        $response->assertJsonPath('message', null);
        $response->assertJsonPath('data.type', 'bearer');
        $response->assertJsonStructure(['message', 'data' => ['token', 'type', 'expires_in']]);
    }

    /**
     * @test
     * @depends endpoint_shouldReturn_201
     */
    public function endpoint_shouldIssueBearerTokenProvidingAccessToAuthOnlyEndpoints()
    {
        factory(User::class)->create([
            'email' => 'unique@email.com',
            'password' => bcrypt('password')
        ]);

        app('router')->get('/api/test-route', function () {
            return response()->success(auth()->user()->email);
        })->middleware('auth:sanctum');

        $response = $this->getJson(
            '/api/test-route'
        ); // Trying to reach protected route without token, expecting 401

        $response->assertStatus(401);
        $response->assertExactJson([
            'message' => 'Unauthenticated.',
            'errors' => null
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

        $response = $this->getJson(
            '/api/test-route',
            [
                'Authorization' => "Bearer $token",
            ]
        ); // Again trying to reach protected route but with token, expecting 200

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'unique@email.com',
            'data' => null
        ]);
    }

    /** @test */
    public function endpoint_withWrongEmail_shouldReturn_401()
    {
        factory(User::class)->create([
            'email' => 'unique@email.com',
            'password' => 'password'
        ]);

        $response = $this->postJson(
            '/api/auth/tokens',
            [
                'email' => 'wrong@email.com',
                'password' => 'password',
                'device_name' => 'ios'
            ]
        );

        $response->assertStatus(401);
        $response->assertExactJson([
            'message' => 'These credentials do not match our records.',
            'errors' => null
        ]);
    }

    /** @test */
    public function endpoint_withWrongPassword_shouldReturn_401()
    {
        factory(User::class)->create([
            'email' => 'unique@email.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson(
            '/api/auth/tokens',
            [
                'email' => 'unique@email.com',
                'password' => 'wrong_password',
                'device_name' => 'ios'
            ]
        );

        $response->assertStatus(401);
        $response->assertExactJson([
            'message' => 'These credentials do not match our records.',
            'errors' => null
        ]);
    }

    /** @test */
    public function endpoint_withoutData_shouldReturn_422()
    {
        $response = $this->postJson(
            '/api/auth/tokens',
            []
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['email' => 'The email field is required.']);
        $response->assertJsonValidationErrors(['password' => 'The password field is required.']);
        $response->assertJsonValidationErrors(['device_name' => 'The device name field is required.']);
    }

    /** @test */
    public function endpoint_withInvalidEmail_shouldReturn_422()
    {
        $response = $this->postJson(
            '/api/auth/tokens',
            [
                'email' => 'invalid-email'
            ]
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['email' => 'The email must be a valid email address.']);
    }
}
