<?php

namespace Tests\Feature\User;

use Mockery\MockInterface;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;

class UserMeTest extends TestCase
{
    /** @test */
    public function endpoint_withoutAuth_shouldReturn_401()
    {
        $response = $this->getJson(
            "/api/users/me"
        );

        $response->assertResponseError('Unauthenticated.', 401);
    }

    /** @test */
    public function endpoint_withoutVerifiedEmail_shouldReturn_403()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);

        $this->actingAs($user);

        $response = $this->getJson(
            "/api/users/me"
        );

        $response->assertResponseError('Your email address is not verified.', 403);
    }
}
