<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdatePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Models\Profile;

/**
 * @group User
 *
 * Endpoints for User entity
 */
class UserController extends Controller
{

    /**
     * Create a new UserController instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:sanctum',
            [
                'except' =>
                    [
                        'store'
                    ]
            ]
        );

        $this->middleware(
            'verified',
            [
                'except' =>
                    [
                        'store'
                    ]
            ]
        );
    }

    /**
     * Me
     *
     * Return currently logged in User
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        $user->loadIncludes();

        return response()->resource(new UserResource($user));
    }

    /**
     * Register
     *
     * Store newly created User.
     * @param  StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = \DB::transaction(function () use ($request) {
            $user = $request->fill(new User);
            $user->password = bcrypt($request->password);

            $user->save();

            $profile = $request->fill(new Profile);
            if ($avatar = $request->file('avatar')) {
                $profile->avatar = $avatar->store(config('storage.profiles.avatar'));
            }

            $profile->user()->associate($user);
            $profile->save();

            $user->assignRole('admin');
            $user->assignRole('user');

            return $user;
        });

        event(new Registered($user));
        return response()->success(__('auth.success_registration'));
    }

    /**
     * Update
     *
     * Update currently logged in User
     * @param  UpdateUserRequest  $request
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = \DB::transaction(function () use ($request) {
            $user = auth()->user();

            $user->update();
            $profile = $request->fill($user->profile);
            
            if ($request->file('avatar')) {
                $avatar = $profile->getOriginal('avatar');
                if ($avatar != 'user.png') {
                    \Storage::delete($avatar);
                }
                $profile->avatar = $request->file("avatar")->store(config("storage.profiles.avatar"));
            }
            $profile->update();

            return $user;
        });

        return response()->resource(new UserResource($user));
    }

    /**
     * Update password
     *
     * Update password for currently logged in User
     * @return JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();
        if (!password_verify($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('passwords.invalid')],
            ]);
        }

        $user->update(['password' => bcrypt($request->new_password)]);

        return response()->resource(new UserResource($user));
    }
}
