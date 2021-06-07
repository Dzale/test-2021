<?php

namespace Tests\Feature\Test;

use Mockery\MockInterface;
use Tests\TestCase;
use App\Models\Test;
use App\Models\User;
use App\Http\Requests\Test\StoreTestRequest;

class TestStoreTest extends TestCase
{
    /** @test */
    public function endpoint_shouldStoreEntityInDatabase()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        \Gate::before(function () {
            return true;
        }); //no need to test authorizations here, this is handled via different test

        $count = Test::count();
        $test = factory(Test::class)->make();
        $data = $test->getAttributes();
        $response = $this->postJson(
            "/api/tests/",
            $data
        );

        $this->assertSame($count + 1, Test::count());
        $response->assertResource($test);
    }

    /** @test */
    public function endpoint_withoutPermission_shouldReturn_401()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);
        $this->mock(
            StoreTestRequest::class,
            function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('validate')->andReturn(true);
                $mock->shouldReceive('user')->andReturn($user);
            }
        );

        $response = $this->postJson(
            "/api/tests/"
        );

        $response->assertResponseError('This action is unauthorized.', 401);
    }

    /** @test */
    public function endpoint_withoutAuth_shouldReturn_401()
    {
        $response = $this->postJson(
            "/api/tests/"
        );

        $response->assertResponseError('Unauthenticated.', 401);
    }

    /** @test */
    public function endpoint_withoutVerifiedEmail_shouldReturn_403()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);

        $this->actingAs($user);

        $response = $this->postJson(
            "/api/tests/"
        );

        $response->assertResponseError('Your email address is not verified.', 403);
    }
}
