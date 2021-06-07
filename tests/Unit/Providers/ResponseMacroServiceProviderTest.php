<?php

namespace Tests\Unit\Providers;

use App\Providers\ResponseMacroServiceProvider;
use Mockery\MockInterface;
use Illuminate\Routing\ResponseFactory;
use Tests\TestCase;

class ResponseMacroServiceProviderTest extends TestCase
{
    /** @test */
    public function provider_shouldBeLoaded()
    {
        $loadedProviders = app()->getLoadedProviders();
        $this->assertArrayHasKey(ResponseMacroServiceProvider::class, $loadedProviders);
        $this->assertTrue($loadedProviders[ResponseMacroServiceProvider::class]);
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function success_withoutMessage_shouldReturnJson()
    {
        $response = app(ResponseFactory::class);
        $result = $response->success();

        $this->assertSame(200, $result->status());
        $this->assertJsonStringEqualsJsonString('{
                "message":null,
                "data":null
            }', $result->getContent());
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function success_shouldReturnJson()
    {
        $response = app(ResponseFactory::class);
        $result = $response->success('Test');

        $this->assertSame(200, $result->status());
        $this->assertJsonStringEqualsJsonString('{
                "message":"Test",
                "data":null
            }', $result->getContent());
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function error_withArguments_shouldReturnJson()
    {
        $errors = [
            'field1' => [
                'error1',
                'error2',
            ],
            'field2' => [
                'error3',
                'error4',
            ]
        ];

        $response = app(ResponseFactory::class);
        $result = $response->error(422, 'Message', $errors);

        $this->assertSame(422, $result->status());
        $this->assertJsonStringEqualsJsonString('{
                "message":"Message",
                "errors":{
                    "field1":[
                        "error1",
                        "error2"
                    ],
                    "field2":[
                        "error3",
                        "error4"
                    ]
                }
            }', $result->getContent());
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function error_withoutArguments_shouldReturnJson()
    {
        $response = app(ResponseFactory::class);
        $result = $response->error();

        $this->assertSame(400, $result->status());
        $this->assertJsonStringEqualsJsonString('{
                "message":"Bad Request",
                "errors":null
            }', $result->getContent());
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function error_withStatus_shouldReturnJson()
    {
        $response = app(ResponseFactory::class);
        $result = $response->error(401);

        $this->assertSame(401, $result->status());
        $this->assertJsonStringEqualsJsonString('{
                "message":"Unauthorized",
                "errors":null
            }', $result->getContent());
    }
}
