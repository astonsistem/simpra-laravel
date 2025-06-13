<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SyncApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sinkronisasi_id' => 'required|string',
            'sinkronisasi_nama' => 'required|string',
            'sinkronisasi_menu' => 'required|string',
            'sinkronisasi_status' => 'required|string',
            'sinkronisasi_param' => 'nullable|array',
            'sinkronisasi_api' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'sinkronisasi_id.required'      => 'sinkronisasi_id is required',
            'sinkronisasi_nama.required'    => 'sinkronisasi_nama is required',
            'sinkronisasi_menu.required'    => 'sinkronisasi_menu is required',
            'sinkronisasi_status.required'  => 'sinkronisasi_status is required',
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
