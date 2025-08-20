<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateDataSelisihRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "tgl_setor" => 'required|string',
            "jenis" => 'required|string',
            "cara_pembayaran" => 'required|string',
            "bank_tujuan" => 'required|string',
            "jumlah" => 'required|numeric',
            "admin_kredit" => 'required|string',
            "admin_debit" => 'required|string',
            "penyetor" => 'required|string',
            "rek_id" => 'required|string',
            "sumber_transaksi"  => 'required|string',
            "klasifikasi"  => 'required|string',
            "tgl_buktibayar"  => 'required|string',
            "no_buktibayar"  => 'required|string',
            "nilai"  => 'required|string',
            "kasir_nama"  => 'required|string',
            "loket_nama"  => 'required|string',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tgl_setor.required' => 'Tanggal Setor is required',
            'jenis.required' => 'Jenis is required',
            'cara_pembayaran.required' => 'Cara Pembayaran is required',
            'bank_tujuan.required' => 'Bank Tujuan is required',
            'jumlah.required' => 'Jumlah Setor is required',
            'admin_kredit.required' => 'Admin Kredit is required',
            'admin_debit.required' => 'Admin Debit is required',
            'penyetor.required' => 'Penyetor is required',
            'rek_id.required' => 'Rek ID is required',
            'sumber_transaksi.required' => 'Sumber Transaksi is required',
            'klasifikasi.required' => 'Klasifikasi is required',
            'tgl_buktibayar.required' => 'Tanggal Bukti Bayar is required',
            'no_buktibayar.required' => 'Nomor Bukti Bayar is required',
            'nilai.required' => 'Nilai is required',
            'kasir_nama.required' => 'Nama Kasir is required',
            'loket_nama.required' => 'Nama Loket is required'
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
