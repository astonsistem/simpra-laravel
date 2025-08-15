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
            'pendaftaran_id' => 'required|integer',
            'no_pendaftaran' => 'required|string',
            'tgl_pendaftaran' => 'required|string',
            'pasien_id' => 'required|integer',
            'no_rekam_medik' => 'required|string',
            'pasien_nama' => 'required|string',
            'pasien_alamat' => 'required|string',
            'jenis_tagihan' => 'required|string',
            'tgl_krs' => 'required|string',
            'tgl_pelayanan' => 'required|string',
            'carabayar_id' => 'required|integer',
            'carabayar_nama' => 'required|string',
            'penjamin_id' => 'required|integer',
            'penjamin_nama' => 'required|string',
            'instalasi_id' => 'required|integer',
            'instalasi_nama' => 'required|string',
            'metode_bayar' => 'required|string',
            'tandabuktibayar_id' => 'required|integer',
            'no_buktibayar' => 'required|string',
            'tgl_buktibayar' => 'required|string',
            'sep_id' => 'required|string',
            'no_sep' => 'required|string',
            'tgl_sep' => 'required|string',
            'cara_pembayaran' => 'required|string',
            'bank_tujuan' => 'required|string',
            'admin_kredit' => 'required|string',
            'admin_debit' => 'required|string',
            'kartubank_pasien' => 'required|string',
            'no_kartubank_pasien' => 'required|string',
            'closingkasir_id' => 'required|string',
            'tgl_closingkasir' => 'required|string',
            'no_closingkasir' => 'required|string',
            'kasir_id' => 'required|integer',
            'kasir_nama' => 'required|string',
            'loket_id' => 'required|integer',
            'loket_nama' => 'required|string',
            'guna_bayar' => 'required|string',
            'total' => 'required|string',
            'klasifikasi' => 'required|string',
            'status_id' => 'required|integer',
            'bulan_mrs' => 'required|string',
            'bulan_krs' => 'required|string',
            'bulan_pelayanan' => 'required|string',
            'akun_id' => 'required|integer',
            'selisih' => 'required|numeric',
            'jumlah_netto' => 'required|numeric',
            'rc_id' => 'required|integer',
            'is_web_change' => 'required|boolean'
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
