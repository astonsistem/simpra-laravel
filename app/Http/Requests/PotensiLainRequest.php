<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PotensiLainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl' => 'required|string',
            'ket' => 'required|string',
            'no_dokumen' => 'required|string',
            'tgl_dokumen' => 'required|string',
            'akun_id' => 'required',
            'pihak3' => 'required|string',
            'pihak3_alamat' => 'required|string',
            'pihak3_telp' => 'required|string',
            'uraian' => 'required|string',
            'tgl_berlaku' => 'required|string',
            'tgl_akhir' => 'required|string',
            'jatuh_tempo' => 'required',
            'besaran_per_satuan' => 'required',
            'total' => 'required',
            'total_pdd' => 'required',
            'total_piutang' => 'required',
            'reklas_pdd' => 'required',
            'pembayaran_piutang' => 'required',
            'monev_id' => 'required',
            'terbayar' => 'required|string',
            'is_web_change' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'tgl.required'                   => 'tgl is required',
            'ket.required'                   => 'ket is required',
            'no_dokumen.required'            => 'no_dokumen is required',
            'tgl_dokumen.required'           => 'tgl_dokumen is required',
            'akun_id.required'               => 'akun_id is required',
            'pihak3.required'                => 'pihak3 is required',
            'pihak3_alamat.required'         => 'pihak3_alamat is required',
            'pihak3_telp.required'           => 'pihak3_telp is required',
            'uraian.required'                => 'uraian is required',
            'tgl_berlaku.required'           => 'tgl_berlaku is required',
            'tgl_akhir.required'             => 'tgl_akhir is required',
            'jatuh_tempo.required'           => 'jatuh_tempo is required',
            'besaran_per_satuan.required'    => 'besaran_per_satuan is required',
            'total.required'                 => 'total is required',
            'total_pdd.required'             => 'total_pdd is required',
            'total_piutang.required'         => 'total_piutang is required',
            'reklas_pdd.required'            => 'reklas_pdd is required',
            'pembayaran_piutang.required'    => 'pembayaran_piutang is required',
            'monev_id.required'              => 'monev_id is required',
            'terbayar.required'              => 'terbayar is required',
            'is_web_change.required'         => 'is_web_change is required'
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
