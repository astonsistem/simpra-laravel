<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_id' => 'required|string',
            'bank_nama' => 'required|string',
            'is_aktif' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_id.required' => 'Bank ID harus diisi.',
            'bank_nama.required' => 'Nama bank harus diisi.',
            'is_aktif.required' => 'Status aktif harus diisi.',
        ];
    }
}