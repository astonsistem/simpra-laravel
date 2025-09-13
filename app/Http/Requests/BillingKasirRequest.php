<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class BillingKasirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {

        $this->prepareFromId('kasir_id', 'kasir_nama', 'master_kasir');
        $this->prepareFromId('loket_id', 'loket_nama', 'master_loket');
        $this->prepareFromId('instalasi_id', 'instalasi_nama', 'master_instalasi');
        $this->prepareFromId('penjamin_id', 'penjamin_nama', 'master_penjamin');

        // convert format from 'd/m/Y' => 'Y-m-d'
        foreach(['tgl_closingkasir', 'tgl_buktibayar', 'tgl_pelayanan', 'tgl_pendaftaran', 'tgl_krs'] as $key) {
            if(request()->has($key) && request($key)) {
                request()->merge([$key => date('Y-m-d', strtotime( str_replace('/', '-', request($key)) ))]);
            }
        }

        if(request()->has('cara_pembayaran') && request()->cara_pembayaran) {
            request()->merge([
                'metode_bayar' => request()->cara_pembayaran,
            ]);
        }

        if(request()->has('status_id') && $status = DB::table('master_status')->where('status_id', request()->status_id)->value('status_nama')) {
            request()->merge([
                'status' => $status,
            ]);
        }
    }


    private function prepareFromId($id, $name, $table)
    {
        if(request()->has($id) && $value = DB::table($table)->where($id, $this->$id)->value($name)) {
            request()->merge([$name => $value]);
        }
    }

    public function rules(): array
    {
        return [
            'no_buktibayar'         => 'nullable',
            'tgl_buktibayar'        => 'required|date',
            'total'                 => 'required|numeric',
            'admin_kredit'          => 'required|numeric',
            'admin_debit'           => 'required|numeric',
            'selisih'               => 'required|numeric',
            'jumlah_netto'          => 'nullable',
            'cara_pembayaran'       => 'required',
            'bank_tujuan'           => 'required',
            'no_kartubank_pasien'   => 'nullable',
            'kartubank_pasien'      => 'nullable',
            //
            'loket_id'              => 'nullable',
            'loket_nama'            => 'nullable',
            'kasir_id'              => 'nullable',
            'kasir_nama'            => 'nullable',
            'no_closingkasir'       => 'nullable',
            'tgl_closingkasir'      => 'nullable',
            'tgl_pendaftaran'       => 'nullable',
            'no_pendaftaran'        => 'nullable',
            'tgl_krs'               => 'nullable',
            'tgl_pelayanan'         => 'nullable',
            'pasien_nama'           => 'nullable',
            'no_rekam_medik'        => 'nullable',
            'pasien_alamat'         => 'nullable',
            //
            'instalasi_id'          => 'nullable',
            'jenis_tagihan'         => 'required',
            'carabayar_id'          => 'nullable',
            'penjamin_id'           => 'nullable',
            'status_id'             => 'nullable',
            'status'                => 'nullable',
            'klasifikasi'           => 'nullable',
            'rc_id'                 => 'nullable',
            'rek_id'                => 'nullable',
        ];
    }

    public function attributes(): array
    {
        return config('attributes');
    }
}
