<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PenerimaanLainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_dokumen' => 'required|string',
            'tgl_dokumen' => 'required|date',
            'akun_id' => 'required|string',
            'pihak3' => 'nullable|string|max:255',
            'pihak3_alamat' => 'nullable|string|max:255',
            'pihak3_telp' => 'nullable|string|max:50',
            'uraian' => 'nullable|string',
            'tgl_bayar' => 'nullable|date',
            'no_bayar' => 'nullable|string',
            'sumber_transaksi' => 'nullable|string',
            'transaksi_id' => 'nullable|string',
            'metode_pembayaran' => 'nullable|string',
            'total' => 'nullable|numeric',
            'pendapatan' => 'nullable|numeric',
            'pdd' => 'nullable|string',
            'piutang' => 'nullable|string',
            'cara_pembayaran' => 'nullable|string',
            'bank_tujuan' => 'nullable|string',
            'admin_kredit' => 'nullable|numeric',
            'admin_debit' => 'nullable|numeric',
            'kartubank' => 'nullable|string',
            'no_kartubank' => 'nullable|string',
            'rc_id' => 'nullable|integer',
            'selisih' => 'nullable|numeric',
            'jumlah_netto' => 'nullable|numeric',
            'desc_piutang_pelayanan' => 'nullable|string',
            'desc_piutang_lain' => 'nullable|string',
            'piutang_id' => 'nullable|string',
            'piutanglain_id' => 'nullable|string',
            'akun_data' => 'nullable|array',
            'akun_data.akun_id' => 'nullable|string',
            'akun_data.akun_kode' => 'nullable|string',
            'akun_data.akun_nama' => 'nullable|string',
            'is_web_change' => 'nullable|boolean',
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
