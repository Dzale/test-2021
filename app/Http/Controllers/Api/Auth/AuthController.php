<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

/**
 * @group Auth CSRF
 *
 * Endpoints for Auth CSRF entity
 */

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['logout']]);
    }

    /**
     * Login
     *
     * CSRF login
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!auth()->attempt($credentials)) {
            throw new AuthenticationException(__('auth.failed'));
        }

        return response()->success();
    }

    /**
     * Logout
     *
     * CSRF logout
     * @return JsonResponse
     * @authenticated
     */
    public function logout(): JsonResponse
    {
        auth()->guard('web')->logout();

        return response()->success();
    }
}
