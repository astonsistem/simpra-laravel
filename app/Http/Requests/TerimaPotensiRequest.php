<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TerimaPotensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'akun_id'               => 'required|integer',
            'tgl_bayar'             => 'required|string',
            'no_bayar'              => 'required|string',
            'pihak3'                => 'required|string',
            'uraian'                => 'required|string',
            'no_dokumen'            => 'required|string',
            'tgl_dokumen'           => 'required|string',
            'total'                 => 'required|numeric',
            'cara_pembayaran'       => 'required|string',
            'bank_tujuan'           => 'required|string',
            'admin_kredit'          => 'required|numeric',
            'admin_debit'           => 'required|numeric',
            'selisih'               => 'required|numeric',
            'jumlah_netto'          => 'required|numeric',
            'pendapatan'            => 'required|numeric',
            'pdd'                   => 'required|numeric',
            'piutang'               => 'required|numeric',
            'desc_piutang_pelayanan'=> 'nullable|string',
            'desc_piutang_lain'     => 'nullable|string',
            'piutang_id'            => 'nullable|string',
            'piutanglain_id'        => 'nullable|integer',
            'sumber_transaksi'      => 'nullable|string',
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
