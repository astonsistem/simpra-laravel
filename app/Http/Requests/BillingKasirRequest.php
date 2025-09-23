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
            'no_buktibayar'         => 'required',
            'tgl_buktibayar'        => 'required',
            'total'                 => 'required',
            'admin_kredit'          => 'required',
            'admin_debit'           => 'required',
            'selisih'               => 'nullable',
            'jumlah_netto'          => 'required',
            'cara_pembayaran'       => 'required',
            'bank_tujuan'           => 'required',
            'no_kartubank_pasien'   => 'required',
            'kartubank_pasien'      => 'required',
            //
            'loket_id'              => 'required',
            'loket_nama'            => 'required',
            'kasir_id'              => 'required',
            'kasir_nama'            => 'required',
            'no_closingkasir'       => 'required',
            'tgl_closingkasir'      => 'required',
            'tgl_pendaftaran'       => 'required',
            'no_pendaftaran'        => 'required',
            'tgl_krs'               => 'required',
            'tgl_pelayanan'         => 'required',
            'pasien_nama'           => 'required',
            'no_rekam_medik'        => 'required',
            'pasien_alamat'         => 'required',
            //
            'instalasi_id'          => 'nullable',
            'jenis_tagihan'         => 'nullable',
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
