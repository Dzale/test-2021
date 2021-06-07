<?php

namespace App\Providers;

use Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Str;

class RequestMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Request::macro('fillable', function (Model $model, string $scope = null): array {
            $keys = array_keys($this->rules());
            $fillable = $model->getFillable();

            if ($scope) {
                if (substr($scope, -1) === '.') {
                    $scope = substr($scope, 0, -1);
                }

                $keys = array_filter($keys, function ($key) use ($scope) {
                    return Str::startsWith($key, $scope);
                });

                $result = data_get($this->only($keys), $scope);
                if (Str::contains($scope, '*')) {
                    $result = Arr::collapse($result);
                }
            } else {
                $result = $this->only($keys);
            }

            $result = Arr::only($result, $fillable);

            if (Str::contains($scope, '*')) {
                $transformed = [];
                foreach ($result as $attr => $values) {
                    foreach ($values as $i => $value) {
                        $transformed[$i][$attr] = $value;
                    }
                }
                $result = $transformed;
            }
            return $result;
        });

        Request::macro('fill', function (Model $model, string $scope = null): Model {
            $fillable = $this->fillable($model, $scope);

            if (!$fillable || !count($fillable)) {
                return $model;
            }

            if (count($fillable) !== count($fillable, COUNT_RECURSIVE)) {
                throw new InvalidArgumentException(__('request.fill_with_array_failed'));
            } else {
                $model->fill($fillable);

                return $model;
            }
        });

        Request::macro('includes', function (): ?string {
            return config('resources.auto_includes.enabled') ?
                $this->get(config('resources.auto_includes.request_key')) :
                null;
        });
    }
}
