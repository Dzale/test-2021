<?php

namespace Tests\Unit\Providers;

use App\Providers\RequestMacroServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Mockery\MockInterface;
use Tests\TestCase;

class RequestMacroServiceProviderTest extends TestCase
{
    /** @test */
    public function provider_shouldBeLoaded()
    {
        $loadedProviders = app()->getLoadedProviders();
        $this->assertArrayHasKey(RequestMacroServiceProvider::class, $loadedProviders);
        $this->assertTrue($loadedProviders[RequestMacroServiceProvider::class]);
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function fillable_withoutScope_shouldReturnArray()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn(
                    [
                        'name' => 'required',
                        'email' => 'required',
                        'password' => 'required',
                        'phone' => 'required'
                    ]
                );
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968'
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968'
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->expects('getFillable')->andReturn(['name', 'email', 'phone', 'dob']);
            }
        );

        $result = $request->fillable($model);

        $this->assertSame([
            'name' => 'test',
            'email' => 'email@email.com',
            'phone' => '00381643253968'
        ], $result);
    }

    /**
     * @test
     * @depends provider_shouldBeLoaded
     */
    public function fillable_withScope_shouldReturnArray()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn([
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'organisation.name' => 'required',
                    'organisation.website' => 'required'
                ]);
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com'
                    ]
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '0038164123456',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                    ]
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->expects('getFillable')->andReturn(['name', 'website']);
            }
        );

        $result = $request->fillable($model, 'organisation');

        $this->assertSame([
            'name' => 'organisation',
            'website' => 'https://test.organisation.com'
        ], $result);
    }

    /**
     * @test
     * @depends fillable_withScope_shouldReturnArray
     */
    public function fillable_withMultiDepthScope_shouldReturnArray()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn([
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'organisation.name' => 'required',
                    'organisation.website' => 'required',
                    'organisation.industry.name' => 'required',
                    'organisation.industry.category' => 'required'
                ]);
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category'
                        ]
                    ]
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category',
                            'priority' => 'test.priority'
                        ],
                        'sectors' => [
                            [
                                'name' => 'sector1',
                                'type' => 'test.type1',
                                'address' => 'test.address1'
                            ],
                            [
                                'name' => 'sector2',
                                'type' => 'test.type2',
                                'address' => 'test.address2'
                            ]
                        ]
                    ]
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->expects('getFillable')->andReturn(['name', 'category']);
            }
        );

        $result = $request->fillable($model, 'organisation.industry');

        $this->assertSame([
            'name' => 'industry',
            'category' => 'test.category',
        ], $result);
    }

    /**
     * @test
     * @depends fillable_withScope_shouldReturnArray
     */
    public function fillable_withMultiDepthScopeAndArray_shouldReturnArray()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn([
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'organisation.name' => 'required',
                    'organisation.website' => 'required',
                    'organisation.industry.name' => 'required',
                    'organisation.industry.category' => 'required',
                    'organisation.sectors.*.name' => 'required',
                    'organisation.sectors.*.address' => 'required',
                ]);
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category',
                            'priority' => 'test.priority'
                        ],
                        'sectors' => [
                            [
                                'name' => 'sector1',
                                'type' => 'test.type1',
                                'address' => 'test.address1'
                            ],
                            [
                                'name' => 'sector2',
                                'type' => 'test.type2',
                                'address' => 'test.address2'
                            ]
                        ]
                    ]
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category',
                            'priority' => 'test.priority'
                        ],
                        'sectors' => [
                            [
                                'name' => 'sector1',
                                'type' => 'test.type1',
                                'address' => 'test.address1'
                            ],
                            [
                                'name' => 'sector2',
                                'type' => 'test.type2',
                                'address' => 'test.address2'
                            ]
                        ]
                    ]
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->expects('getFillable')->andReturn(['name', 'address']);
            }
        );

        $result = $request->fillable($model, 'organisation.sectors.*');
        $this->assertSame([
            [
                'name' => 'sector1',
                'address' => 'test.address1'
            ],
            [
                'name' => 'sector2',
                'address' => 'test.address2'
            ],
        ], $result);
    }

    /**
     * @test
     * @depends fillable_withoutScope_shouldReturnArray
     */
    public function fill_withoutScope_shouldFillAndReturnModel()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn(
                    [
                        'name' => 'required',
                        'email' => 'required',
                        'password' => 'required',
                        'phone' => 'required'
                    ]
                );
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968'
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968'
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('getFillable')->andReturn(['name', 'email', 'phone', 'dob']);
            }
        )->makePartial();

        $result = $request->fill($model);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertSame('test', $result->name);
        $this->assertSame('email@email.com', $result->email);
        $this->assertSame('00381643253968', $result->phone);
    }

    /**
     * @test
     * @depends fill_withoutScope_shouldFillAndReturnModel
     */
    public function fill_withScope_shouldFillAndReturnModel()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn([
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'organisation.name' => 'required',
                    'organisation.website' => 'required'
                ]);
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com'
                    ]
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '0038164123456',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                    ]
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('getFillable')->andReturn(['name', 'website']);
            }
        )->makePartial();

        $result = $request->fill($model, 'organisation');

        $this->assertInstanceOf(Model::class, $result);
        $this->assertSame('organisation', $result->name);
        $this->assertSame('https://test.organisation.com', $result->website);
        $this->assertNull($result->fax);
    }

    /**
     * @test
     * @depends fill_withoutScope_shouldFillAndReturnModel
     */
    public function fill_withMultiDepthScope_shouldFillAndReturnModel()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn([
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'organisation.name' => 'required',
                    'organisation.website' => 'required',
                    'organisation.industry.name' => 'required',
                    'organisation.industry.category' => 'required'
                ]);
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category'
                        ]
                    ]
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category',
                            'priority' => 'test.priority'
                        ],
                        'sectors' => [
                            [
                                'name' => 'sector1',
                                'type' => 'test.type1',
                                'address' => 'test.address1'
                            ],
                            [
                                'name' => 'sector2',
                                'type' => 'test.type2',
                                'address' => 'test.address2'
                            ]
                        ]
                    ]
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('getFillable')->andReturn(['name', 'category']);
            }
        )->makePartial();

        $result = $request->fill($model, 'organisation.industry');

        $this->assertInstanceOf(Model::class, $result);
        $this->assertSame('industry', $result->name);
        $this->assertSame('test.category', $result->category);
        $this->assertNull($result->priority);
    }

    /**
     * @test
     * @depends fill_withMultiDepthScope_shouldFillAndReturnModel
     */
    public function fill_withMultiDepthScopeAndArray_shouldThrow()
    {
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) {
                $mock->expects('rules')->andReturn([
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'organisation.name' => 'required',
                    'organisation.website' => 'required',
                    'organisation.industry.name' => 'required',
                    'organisation.industry.category' => 'required',
                    'organisation.sectors.*.name' => 'required',
                    'organisation.sectors.*.address' => 'required',
                ]);
                $mock->shouldReceive('input')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category',
                            'priority' => 'test.priority'
                        ],
                        'sectors' => [
                            [
                                'name' => 'sector1',
                                'type' => 'test.type1',
                                'address' => 'test.address1'
                            ],
                            [
                                'name' => 'sector2',
                                'type' => 'test.type2',
                                'address' => 'test.address2'
                            ]
                        ]
                    ]
                ]);
                $mock->shouldReceive('all')->andReturn([
                    'name' => 'test',
                    'email' => 'email@email.com',
                    'password' => 'password',
                    'phone' => '00381643253968',
                    'organisation' => [
                        'name' => 'organisation',
                        'website' => 'https://test.organisation.com',
                        'fax' => '0038162654321',
                        'industry' => [
                            'name' => 'industry',
                            'category' => 'test.category',
                            'priority' => 'test.priority'
                        ],
                        'sectors' => [
                            [
                                'name' => 'sector1',
                                'type' => 'test.type1',
                                'address' => 'test.address1'
                            ],
                            [
                                'name' => 'sector2',
                                'type' => 'test.type2',
                                'address' => 'test.address2'
                            ]
                        ]
                    ]
                ]);
            }
        )->makePartial();

        $model = $this->mock(
            Model::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('getFillable')->andReturn(['name', 'address']);
            }
        )->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to fill. Can\'t fill model with multiple resources!');
        $request->fill($model, 'organisation.sectors.*');
    }
}
