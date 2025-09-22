<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidasiCancelPenerimaanLainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'    => 'required|string',
            'rc_id' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'id'    => 'ID penerimaan layanan',
            'rc_id' => 'RC ID',
        ];
    }
}
