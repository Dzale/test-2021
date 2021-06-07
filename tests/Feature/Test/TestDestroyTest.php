<?php

namespace Tests\Feature\Test;

use Mockery\MockInterface;
use Tests\TestCase;
use App\Models\Test;
use App\Models\User;

class TestDestroyTest extends TestCase
{
    /** @test */
    public function endpoint_shouldRemoveEntityFromDatabase()
    {
        $user = factory(User::class)->create();
        $test = factory(Test::class)->create();

        $this->actingAs($user);

        \Gate::before(function () {
            return true;
        }); //no need to test authorizations here, this is handled via different test

        $count = Test::count();
        $response = $this->deleteJson(
            "/api/tests/{$test->id}"
        );

        $this->assertSame($count - 1, Test::count());
        $response->assertSuccessful();
    }

    /** @test */
    public function endpoint_withoutPermission_shouldReturn_401()
    {
        $user = factory(User::class)->create();
        $test = factory(Test::class)->create();

        $this->actingAs($user);

        $response = $this->deleteJson(
            "/api/tests/{$test->id}"
        );

        $response->assertResponseError('This action is unauthorized.', 401);
    }

    /** @test */
    public function endpoint_withInvalidUrlParameters_shouldReturn_404()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $response = $this->deleteJson(
            "/api/tests/1337"
        );

        $response->assertResponseError('No query results for model [App\Models\Test] 1337', 404);
    }

    /** @test */
    public function endpoint_withoutAuth_shouldReturn_401()
    {
        $test = factory(Test::class)->create();

        $response = $this->deleteJson(
            "/api/tests/{$test->id}"
        );

        $response->assertResponseError('Unauthenticated.', 401);
    }

    /** @test */
    public function endpoint_withoutVerifiedEmail_shouldReturn_403()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);
        $test = factory(Test::class)->create();

        $this->actingAs($user);

        $response = $this->deleteJson(
            "/api/tests/{$test->id}"
        );

        $response->assertResponseError('Your email address is not verified.', 403);
    }
}
