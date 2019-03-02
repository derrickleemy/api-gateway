<?php

namespace App\Auth;

use App\Auth\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }
}
