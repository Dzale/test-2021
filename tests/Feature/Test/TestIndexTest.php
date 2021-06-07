<?php

namespace Tests\Feature\Test;

use Mockery\MockInterface;
use Tests\TestCase;
use App\Models\Test;
use App\Models\User;

class TestIndexTest extends TestCase
{
    /** @test */
    public function endpoint_shouldReturnPaginatedListOfResources()
    {
        $user = factory(User::class)->create();
        factory(Test::class, 3)->create();
        $this->actingAs($user);

        \Gate::before(function () {
            return true;
        }); //no need to test authorizations here, this is handled via different test

        $response = $this->getJson(
            "/api/tests/"
        );

        $tests = Test::limit(10)->get();
        $response->assertResourceCollection($tests);
    }

    /** @test */
    public function endpoint_withoutPermission_shouldReturn_401()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $response = $this->getJson(
            "/api/tests/"
        );

        $response->assertResponseError('This action is unauthorized.', 401);
    }

    /** @test */
    public function endpoint_withoutAuth_shouldReturn_401()
    {
        $response = $this->getJson(
            "/api/tests/"
        );

        $response->assertResponseError('Unauthenticated.', 401);
    }

    /** @test */
    public function endpoint_withoutVerifiedEmail_shouldReturn_403()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);

        $this->actingAs($user);

        $response = $this->getJson(
            "/api/tests/"
        );

        $response->assertResponseError('Your email address is not verified.', 403);
    }
}
