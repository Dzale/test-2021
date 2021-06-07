<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseMacroServiceProvider extends ServiceProvider
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
        ResponseFactory::macro('success', function (string $message = null): JsonResponse {
            return response()->json([
                'message'  => $message,
                'data' => null,
            ]);
        });

        ResponseFactory::macro('resource', function (
            JsonResource $resource,
            string $message = null
        ): JsonResponse {
            return $resource->response()->message($message);
        });

        JsonResponse::macro('message', function (string $message = null) : self {
            $data = json_decode($this->data, true);
            $data['message'] = $message;
            $this->setData($data);

            return $this;
        });

        ResponseFactory::macro('error', function (
            $status = 400,
            string $message = null,
            array $errors = null
        ): JsonResponse {
            return response()->json([
                'message' => $message ?: __("response.$status"),
                'errors'  => $errors,
            ], $status);
        });
    }
}
