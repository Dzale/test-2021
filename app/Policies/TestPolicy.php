<?php

namespace App\Policies;

use App\Models\Test;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->can('test-view-all')) {
            return Response::allow();
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Test $test
     * @return mixed
     */
    public function view(User $user, Test $test)
    {
        if ($user->can('test-view')) {
            return Response::allow();
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can('test-store')) {
            return Response::allow();
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Test $test
     * @return mixed
     */
    public function update(User $user, Test $test)
    {
        if ($user->can('test-update')) {
            return Response::allow();
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Test $test
     * @return mixed
     */
    public function delete(User $user, Test $test)
    {
        if ($user->can('test-destroy')) {
            return Response::allow();
        }
    }
}
