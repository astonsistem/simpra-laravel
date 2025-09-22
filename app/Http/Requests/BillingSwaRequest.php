<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BillingSwaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        foreach(['tgl_bayar', 'tgl_dokumen'] as $key) {
            if(request()->has($key) && request($key))
            {
                $date = normalize_date(request($key));

                request()->merge([$key => $date]);
            }
        }

        // Default bank tujuan jika kosong dan cara pembayaran tunai maka diisi bank JATIM
        if(request()->has('cara_pembayaran') && strtoupper(request('cara_pembayaran')) == 'TUNAI' && (!request()->has('bank_tujuan') || !request('bank_tujuan')))
        {
            request()->merge(['bank_tujuan' => 'JATIM']);
        }
    }

    public function rules(): array
    {
        return [
            'admin_debit'           => 'required|numeric',          // Biaya Admin QRIS
            'admin_kredit'          => 'required|numeric',          // Biaya Admin EDC
            'akun_id'               => 'required|numeric',          // Jenis Penerimaan
            'bank_tujuan'           => 'required|string',           // Bank Tujuan
            'cara_pembayaran'       => 'required|string',           // Cara Pembayaran
            'jumlah_netto'          => 'required|numeric',          // Jumlah Netto
            'metode_pembayaran'     => 'nullable|numeric',
            'no_bayar'              => 'nullable|string',
            'no_dokumen'            => 'nullable|string',
            'pdd'                   => 'required|numeric',          // PDD
            'pendapatan'            => 'required|numeric',          // Pendapatan
            'pihak3'                => 'nullable|string',
            'piutang'               => 'required|numeric',           // Piutang
            'rek_id'                => 'nullable|numeric',
            'selisih'               => 'required|numeric',          // selisih
            'sumber_transaksi'      => 'nullable|string',
            'tgl_bayar'             => 'required|date',             // tgl. bayar
            'tgl_dokumen'           => 'nullable|date',
            'total'                 => 'required|numeric',          // Jumlah Bruto
            'uraian'                => 'nullable|string',

        ];
    }

    public function attributes(): array
    {
        return array_merge(config('attributes'), [
            'total'     => 'Jumlah Bruto'
        ]);
    }
}
