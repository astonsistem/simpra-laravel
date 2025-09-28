<?php

namespace App\Http\Requests\KurangBayar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

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

        $this->prepareFromId('kasir_id', 'kasir_nama', 'master_kasir');
        $this->prepareFromId('loket_id', 'loket_nama', 'master_loket');
    }

    private function prepareFromId($id, $name, $table)
    {
        if (request()->has($id) && $value = DB::table($table)->where($id, $this->$id)->value($name)) {
            request()->merge([$name => $value]);
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
            'admin_debit'           => 'required|numeric',
            'admin_kredit'          => 'required|numeric',
            'bank_tujuan'           => 'required|string',
            'cara_pembayaran'       => 'required|string',
            'jenis'                 => 'required|string',
            'jumlah'                => 'required|numeric',
            'jumlah_netto'          => 'required|numeric',
            'kasir_id'              => 'nullable',
            'kasir_nama'            => 'nullable',
            'klasifikasi'           => 'nullable',
            'loket_id'              => 'nullable',
            'loket_nama'            => 'nullable',
            'nilai'                 => 'nullable|numeric',
            'no_buktibayar'         => 'nullable|string',
            'penyetor'              => 'nullable|string',
            'rek_id'                => 'nullable',
            'sumber_transaksi'      => 'nullable',
            'sumber_id'             => 'nullable',
            'tgl_buktibayar'        => 'nullable|date',
            'tgl_setor'             => 'required|date',

        ];
    }

    public function attributes()
    {
        return array_merge(config('attributes'), [
            'jumlah'            => 'Jumlah Setor',
            'no_buktibayar'     => 'No. Bukti Bayar',
            'tgl_buktibayar'    => 'Tgl. Bukti Bayar',
            'nilai'             => 'Selisih Kurang',
        ]);
    }
}
