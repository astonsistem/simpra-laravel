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

    protected function prepareForValidation()
    {
        $map = [
            'no_bayar'         => 'no_buktibayar',
            'tgl_bayar'        => 'tgl_buktibayar',
            'pasien'           => 'pasien_nama',
            'no_dokumen'       => 'no_pendaftaran',
            'tgl_dokumen'      => 'tgl_pelayanan',
            'cara_bayar_id'    => 'carabayar_id',
            'jumlah_bruto'     => 'total',
            'biaya_admin_edc'  => 'admin_kredit',
            'biaya_admin_qris' => 'admin_debit',
        ];

        $data = $this->all();

        foreach ($map as $feKey => $beKey) {
            if (isset($data[$feKey])) {
                $data[$beKey] = $data[$feKey];
                unset($data[$feKey]);
            }
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        return [
            'no_buktibayar'  => 'required|string',
            'tgl_buktibayar' => 'required|string',
            'pasien_nama'    => 'required|string',
            'no_pendaftaran' => 'required|string',
            'tgl_pelayanan'  => 'required|string',
            // 'uraian'         => 'required|string',
            'carabayar_id'   => 'required|string',
            'total'          => 'required|int',
            'admin_kredit'   => 'required|int',
            'admin_debit'    => 'required|int',
            'jumlah_netto'   => 'required|int',
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
