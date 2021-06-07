<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        \Mockery::globalHelpers();

        TestResponse::macro('assertResponseError', function (string $message, int $status = 422) {
            $this->assertStatus($status);
            $this->assertJsonPath('message', $message);

            return $this;
        });

        TestResponse::macro('assertResource', function (Model $model) {
            $this->assertSuccessful();

            foreach ($model->toArray() as $key => $value) {
                $path = "data.$key";
                if ($value instanceof File) {
                    Storage::assertExists($this->json($path));
                    continue;
                }
                if ($value instanceof Carbon) {
                    $value = $value->toJSON();
                }
                $this->assertJsonPath($path, $value);
            }

            return $this;
        });

        TestResponse::macro('assertResourceCollection', function (Collection $collection) {
            $this->assertSuccessful();

            foreach ($collection as $i => $model) {
                foreach ($model->toArray() as $key => $value) {
                    if ($value instanceof Carbon) {
                        $value = $value->toJSON();
                    }
                    $this->assertJsonPath("data.$i.$key", $value);
                }
            }

            return $this;
        });
    }
}
