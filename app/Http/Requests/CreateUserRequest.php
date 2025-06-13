<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "nip" => 'required|string',
            "nama" => 'required|string',
            "no_telp" => 'required|string',
            "jabatan" => 'required|string',
            "email" => 'required|string',
            "username" => 'required|string',
            "role" => 'required|string',
            "password" => 'required|string'
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'      => 'Nip is required',
            'nama.required'     => 'Nama is required',
            'no_telp.required'  => 'No Telepon is required',
            'jabatan.required'  => 'Jabatan is required',
            'email.required'    => 'Email is required',
            'username.required' => 'Username is required',
            'role.required'     => 'Role is required',
            'password.required' => 'Password is require'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'detail' => collect($validator->errors())->map(function ($message, $field) {
                return [
                    'loc' => [$field, 0],
                    'msg' => $message[0],
                    'type' => 'validation_error'
                ];
            })->values()
        ], 422));
    }
}
