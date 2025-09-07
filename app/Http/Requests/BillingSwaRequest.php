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
    }

    public function rules(): array
    {
        return [
            'admin_debit'           => 'nullable|numeric',          // 5
            'admin_kredit'          => 'nullable|numeric',          // 4
            'akun_id'               => 'required|numeric',          // 13
            'bank_tujuan'           => 'required|string',           // 9
            'cara_pembayaran'       => 'required|string',           // 8
            'jumlah_netto'          => 'required|numeric',          // 7
            'no_bayar'              => 'required|string',           // 16
            'no_dokumen'            => 'required|string',           // 1
            'pdd'                   => 'nullable|numeric',          // 11
            'pendapatan'            => 'nullable|numeric',          // 10
            'pihak3'                => 'required|string|max:255',   // 17
            'piutang'               => 'nullable|string',           // 12
            'rek_id'                => 'required|numeric',          // 0
            'selisih'               => 'nullable|numeric',          // 6
            'sumber_transaksi'      => 'required|numeric',          // 14
            'tgl_bayar'             => 'required|date',             // 15
            'tgl_dokumen'           => 'required|date',             // 2
            'total'                 => 'required|numeric',          // 3
            'uraian'                => 'nullable|string',           // 18

        ];
    }

    public function attributes(): array
    {
        return array_merge(config('attributes'), [
            'total'     => 'Jumlah Bruto'
        ]);
    }
}
