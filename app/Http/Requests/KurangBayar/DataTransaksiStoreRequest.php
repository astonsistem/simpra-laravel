<?php

namespace App\Http\Requests\KurangBayar;

use Illuminate\Foundation\Http\FormRequest;

class DataTransaksiStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // if jumlah > 0 && ! jumlah_netto
        if ($this->jumlah > 0 && ! $this->jumlah_netto) {
            $this->merge([
                'jumlah_netto' => (int) $this->jumlah - (int) $this->admin_kredit - (int) $this->admin_debit,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tgl_setor'             => 'required|date',
            'tgl_buktibayar'        => 'required|date',
            'no_buktibayar'         => 'required|string',
            'jenis'                 => 'required|string',
            'cara_pembayaran'       => 'required|string',
            'bank_tujuan'           => 'required|string',
            'jumlah'                => 'required|numeric',
            'admin_kredit'          => 'required|numeric',
            'admin_debit'           => 'required|numeric',
            'jumlah_netto'          => 'required|numeric',
            'penyetor'              => 'nullable|string',
            'rek_id'                => 'nullable',
            'sumber_transaksi'      => 'nullable',
            'klasifikasi'           => 'nullable',
        ];
    }

    public function attributes()
    {
        return array_merge(config('attributes'), [
            'jumlah' => 'Jumlah Setor',
        ]);
    }
}
