<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RekeningKoranImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data' => 'required|array',
            'data.*.tgl_rc' => 'required|date_format:Y-m-d',
            'data.*.no_rc' => 'required|string',
            'data.*.uraian' => 'nullable|string',
            'data.*.rek_dari' => 'nullable|string',
            'data.*.nama_dari' => 'nullable|string',
            'data.*.bank' => 'nullable|string',
            'data.*.debit' => 'nullable|numeric',
            'data.*.kredit' => 'nullable|numeric',
        ];
    }
}
