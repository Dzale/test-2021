<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\TokenRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

/**
 * @group Auth Tokens
 *
 * Endpoints for Auth Tokens entity
 */

class TokenController extends Controller
{
    /**
     * Create a new TokenController instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['destroy']]);
        $this->middleware('throttle', ['only' => ['store']]);
    }

    /**
     * Login
     *
     * Issue new token for given credentials.
     * @param TokenRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(TokenRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!auth()->attempt($credentials)) {
            throw new AuthenticationException(__('auth.failed'));
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json(
            [
                'data' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => config('sanctum.expiration')
                ]
            ]
        )->message(null)->setStatusCode(201);
    }

    /**
     * Logout
     *
     * Revoke issued user token
     * @return JsonResponse
     * @authenticated
     */
    public function destroy(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->success();
    }
}
