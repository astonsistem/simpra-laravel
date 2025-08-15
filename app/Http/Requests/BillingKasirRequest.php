<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BillingKasirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
{
    return [
        'id' => 'required|string',
        'noBayar' => 'required|string',
        'tglBayar' => 'required|string',
        'pasien' => 'required|string',
        'uraian' => 'required|string',
        'noDokumen' => 'required|string',
        'tglDokumen' => 'required|string',
        'sumberTransaksi' => 'required|string',
        'instalasi' => 'required|string',
        'metodeBayar' => 'required|string',
        'caraBayar' => 'required|string',
        'rekeningDpa' => 'required|string',
        'bank' => 'required|string',
        'jumlahBruto' => 'required|string',
        'biayaAdminEdc' => 'required|string',
        'biayaAdminQris' => 'required|string',
        'selisih' => 'required|string',
        'jumlahNetto' => 'required|numeric',
    ];
}
    public function messages(): array
    {
        return [];
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
