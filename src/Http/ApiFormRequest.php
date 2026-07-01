<?php

namespace PhpDevUmesh\LaravelApiResponse\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use PhpDevUmesh\LaravelApiResponse\ResponseBuilder;

class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        $builder = app(ResponseBuilder::class);
        $response = $builder->validationError($validator->errors());
        throw new HttpResponseException($response);
    }

    protected function failedAuthorization(): void
    {
        $builder = app(ResponseBuilder::class);
        $response = $builder->error('Forbidden', 403);
        throw new HttpResponseException($response);
    }
}
