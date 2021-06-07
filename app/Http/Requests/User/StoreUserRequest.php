<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Profile\StoreProfileRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        $profileRequest = new StoreProfileRequest;

        return array_merge(
            [
            'email' => "required|email|max:255|unique:users",
            'password' => "required|confirmed|min:6",

        ],
            $profileRequest->rules()
        );
    }
}
