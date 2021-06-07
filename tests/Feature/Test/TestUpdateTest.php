<?php

namespace Tests\Feature\Test;

use Mockery\MockInterface;
use Tests\TestCase;
use App\Models\Test;
use App\Models\User;
use App\Http\Requests\Test\UpdateTestRequest;

class TestUpdateTest extends TestCase
{
    /** @test */
    public function endpoint_shouldUpdateEntityInDatabase()
    {
        $user = factory(User::class)->create();
        $test = factory(Test::class)->create();

        $this->actingAs($user);

        \Gate::before(function () {
            return true;
        }); //no need to test authorizations here, this is handled via different test

        $testModified = factory(Test::class)->make();
        $data = $testModified->getAttributes();
        $response = $this->putJson(
            "/api/tests/{$test->id}",
            $data
        );

        $response->assertResource($testModified);
    }

    /** @test */
    public function endpoint_withoutPermission_shouldReturn_401()
    {
        $user = factory(User::class)->create();
        $test = factory(Test::class)->create();

        $this->actingAs($user);
        $this->mock(
            UpdateTestRequest::class,
            function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('validate')->andReturn(true);
                $mock->shouldReceive('user')->andReturn($user);
            }
        );

        $response = $this->putJson(
            "/api/tests/{$test->id}"
        );

        $response->assertResponseError('This action is unauthorized.', 401);
    }

    /** @test */
    public function endpoint_withoutAuth_shouldReturn_401()
    {
        $test = factory(Test::class)->create();

        $response = $this->putJson(
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

        $response = $this->putJson(
            "/api/tests/{$test->id}"
        );

        $response->assertResponseError('Your email address is not verified.', 403);
    }
}
