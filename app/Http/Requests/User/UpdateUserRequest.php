<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        $profileRequest = new UpdateProfileRequest;

        return array_merge(
            [

        ],
            $profileRequest->rules()
        );
    }
}
