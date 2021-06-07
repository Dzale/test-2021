<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Mockery\MockInterface;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /** @test */
    public function endpoint_shouldReturn_200()
    {
        $this->mock(Factory::class, function (MockInterface $mock) {
            $mock->expects('attempt')->with([
                'email' => 'unique@email.com',
                'password' => 'password'
            ])->andReturn(true);
        });

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

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => null,
            'data' => null
        ]);
    }

    /**
     * @test
     * @depends endpoint_shouldReturn_200
     */
    public function endpoint_shouldIssueCsrfProvidingAccessToAuthOnlyEndpoints()
    {
        factory(User::class)->create([
            'email' => 'unique@email.com',
            'password' => bcrypt('password')
        ]);

        app('router')->get('/api/test-route', function () {
            return response()->success(auth()->user()->email);
        })->middleware('auth:sanctum');

        $response = $this->getJson(
            '/sanctum/csrf-cookie'
        );

        $response->assertCookie('XSRF-TOKEN');

        $response = $this->getJson(
            '/api/test-route'
        );

        $response->assertStatus(401);
        $response->assertExactJson([
            'message' => 'Unauthenticated.',
            'errors' => null
        ]);

        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => 'unique@email.com',
                'password' => 'password'
            ]
        );

        $this->assertAuthenticated('web');

        $response = $this->getJson(
            '/api/test-route'
        );

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
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => 'wrong@email.com',
                'password' => 'password'
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
            '/api/auth/login',
            [
                'email' => 'unique@email.com',
                'password' => 'wrong_password'
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
            '/api/auth/login',
            []
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['email' => 'The email field is required.']);
        $response->assertJsonValidationErrors(['password' => 'The password field is required.']);
    }

    /** @test */
    public function endpoint_withInvalidEmail_shouldReturn_422()
    {
        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => 'invalid-email'
            ]
        );

        $response->assertResponseError('The given data was invalid.');
        $response->assertJsonValidationErrors(['email' => 'The email must be a valid email address.']);
    }
}
