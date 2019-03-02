<?php

namespace App\Auth;

use Illuminate\Contracts\Validation\Validator;
use Urameshibr\Requests\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntityHttpException($validator->errors()->toJson());
    }

    protected function failedAuthorization()
    {
        throw new HttpException(403);
    }
}
