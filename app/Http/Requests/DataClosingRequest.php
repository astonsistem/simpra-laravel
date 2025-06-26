<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DataClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_closing' => 'required|string',
            'no_closing' => 'required|string',
            'vol_tunai' => 'required',
            'vol_transfer_jatim' => 'required',
            'vol_transfer_mandiri' => 'required',
            'vol_transfer_bca' => 'required',
            'vol_transfer_lainnya' => 'required',
            'vol_atm_jatim' => 'required',
            'vol_atm_mandiri' => 'required',
            'vol_atm_bca' => 'required',
            'vol_atm_lainnya' => 'required',
            'vol_edc' => 'required',
            'vol_qris' => 'required',
            'vol_mb' => 'required',
            'vol_ecomm' => 'required',
            'vol_ue' => 'required',
            'tunai' => 'required',
            'transfer_jatim' => 'required',
            'transfer_mandiri' => 'required',
            'transfer_bca' => 'required',
            'transfer_lainnya' => 'required',
            'atm_jatim' => 'required',
            'atm_mandiri' => 'required',
            'atm_bca' => 'required',
            'atm_lainnya' => 'required',
            'edc' => 'required',
            'qris' => 'required',
            'mb' => 'required',
            'ecomm' => 'required',
            'ue' => 'required',
            'rc_id' => 'required',
            'kasir_id' => 'required',
            'kasir_nama' => 'required|string',
            'penyetor_id' => 'required',
            'penyetor_nama' => 'required|string',
            'is_web_change' => 'required',
            'keterangan' => 'required|string'
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_closing.required'          => 'tgl_closing is required',
            'no_closing.required'           => 'no_closing is required',
            'vol_tunai.required'            => 'vol_tunai is required',
            'vol_transfer_jatim.required'   => 'vol_transfer_jatim is required',
            'vol_transfer_mandiri.required' => 'vol_transfer_mandiri is required',
            'vol_transfer_bca.required'     => 'vol_transfer_bca is required',
            'vol_transfer_lainnya.required' => 'vol_transfer_lainnya is required',
            'vol_atm_jatim.required'        => 'vol_atm_jatim is required',
            'vol_atm_mandiri.required'      => 'vol_atm_mandiri is required',
            'vol_atm_bca.required'          => 'vol_atm_bca is required',
            'vol_atm_lainnya.required'      => 'vol_atm_lainnya is required',
            'vol_edc.required'              => 'vol_edc is required',
            'vol_qris.required'             => 'vol_qris is required',
            'vol_mb.required'               => 'vol_mb is required',
            'vol_ecomm.required'            => 'vol_ecomm is required',
            'vol_ue.required'               => 'vol_ue is required',
            'tunai.required'                => 'tunai is required',
            'transfer_jatim.required'       => 'transfer_jatim is required',
            'transfer_mandiri.required'     => 'transfer_mandiri is required',
            'transfer_bca.required'         => 'transfer_bca is required',
            'transfer_lainnya.required'     => 'transfer_lainnya is required',
            'atm_jatim.required'            => 'atm_jatim is required',
            'atm_mandiri.required'          => 'atm_mandiri is required',
            'atm_bca.required'              => 'atm_bca is required',
            'atm_lainnya.required'          => 'atm_lainnya is required',
            'edc.required'                  => 'edc is required',
            'qris.required'                 => 'qris is required',
            'mb.required'                   => 'mb is required',
            'ecomm.required'                => 'ecomm is required',
            'ue.required'                   => 'ue is required',
            'rc_id.required'                => 'rc_id is required',
            'kasir_id.required'             => 'kasir_id is required',
            'kasir_nama.required'           => 'kasir_nama is required',
            'penyetor_id.required'          => 'penyetor_id is required',
            'penyetor_nama.required'        => 'penyetor_nama is required',
            'is_web_change.required'        => 'is_web_change is required',
            'keterangan.required'           => 'keterangan is required'
        ];
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
