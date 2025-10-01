<?php

namespace App\Http\Requests;

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

    public function prepareForValidation()
    {
        // change tgl_rc date format to Y-m-d
        $this->merge([
            'data' => array_map(function ($item) {
                $item['tgl_rc'] = normalize_date($item['tgl_rc']);
                $item['tgl'] = date('Y-m-d');
                $item['sync_at'] = date('Y-m-d H:i:s');
                // remove id
                unset($item['id']);
                return $item;
            }, $this->data),
        ]);
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
            'data.*.tgl' => 'nullable|date_format:Y-m-d',
            'data.*.no_rc' => 'required|string',
            'data.*.uraian' => 'nullable|string',
            'data.*.rek_dari' => 'nullable|string',
            'data.*.nama_dari' => 'nullable|string',
            'data.*.bank' => 'nullable|string',
            'data.*.debit' => 'nullable|numeric',
            'data.*.kredit' => 'nullable|numeric',
            'data.*.sync_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
