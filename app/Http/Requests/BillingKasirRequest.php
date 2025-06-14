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
            'pendaftaran_id' => 'required|int',
            'no_pendaftaran' => 'required|string',
            'tgl_pendaftaran' => 'required|string',
            'pasien_id' => 'required|int',
            'no_rekam_medik' => 'required|string',
            'pasien_nama' => 'required|string',
            'pasien_alamat' => 'required|string',
            'jenis_tagihan' => 'required|string',
            'tgl_krs' => 'required|string',
            'tgl_pelayanan' => 'required|string',
            'carabayar_id' => 'required|int',
            'carabayar_nama' => 'required|string',
            'penjamin_id' => 'required|int',
            'penjamin_nama' => 'required|string',
            'instalasi_id' => 'required|int',
            'instalasi_nama' => 'required|string',
            'metode_bayar' => 'required|string',
            'tandabuktibayar_id' => 'required|int',
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
            'kasir_id' => 'required|int',
            'kasir_nama' => 'required|string',
            'loket_id' => 'required|int',
            'loket_nama' => 'required|string',
            'guna_bayar' => 'required|string',
            'total' => 'required|string',
            'klasifikasi' => 'required|string',
            'status_id' => 'required|int',
            'bulan_mrs' => 'required|string',
            'bulan_krs' => 'required|string',
            'bulan_pelayanan' => 'required|string',
            'akun_id' => 'required|int',
            'selisih' => 'required|int',
            'jumlah_netto' => 'required|int',
            'rc_id' => 'required|int',
            'is_web_change' => 'required'

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
