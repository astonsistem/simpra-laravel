<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AkunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'id' => 'required|string|unique:master_akun,id,' . $id,
            'akun_id' => 'required|string',
            'akun_kode' => 'required|string',
            'akun_nama' => 'required|string',
            'rek_id' => 'nullable|string',
            'rek_nama' => 'nullable|string',
            'akun_kelompok' => 'nullable|string',
            'created_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'akun_id.required'       => 'akun_id harus diisi.',
            'akun_kode.required'     => 'akun_kode harus diisi.',
            'akun_nama.required'     => 'akun_nama harus diisi.',
            'rek_id.required'        => 'rek_id harus diisi.',
            'rek_nama.required'      => 'rek_nama harus diisi.',
            'akun_kelompok.required' => 'akun_kelompok harus diisi.',
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
