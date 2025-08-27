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
        foreach(['tgl_closingkasir', 'tgl_buktibayar', 'tgl_pelayanan', 'tgl_pendaftaran'] as $key) {
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
            'loket_id'       => 'required',
            'loket_nama'       => 'required',
            'kasir_id'       => 'required',
            'kasir_nama'     => 'required',
            'no_closingkasir' => 'required',
            'tgl_closingkasir' => 'required',
            'no_buktibayar'  => 'required',
            'tgl_buktibayar' => 'required',
            'no_pendaftaran'  => 'required',
            'tgl_pelayanan'  => 'required',
            'status_id'     => 'required',
            'status'     => 'required',
            'klasifikasi'         => 'nullable',
            'uraian'         => 'nullable',
            //
            'cara_pembayaran' => 'required',
            'metode_bayar' => 'nullable',
            'bank_tujuan'    => 'required',
            'instalasi_id'   => 'required',
            'instalasi_nama'   => 'required',
            'jenis_tagihan'  => 'required',
            //
            'pasien_nama'    => 'required',
            'no_rekam_medik' => 'nullable',
            'tgl_pendaftaran' => 'nullable',
            'carabayar_id'   => 'required',
            'penjamin_id'   => 'nullable',
            'penjamin_nama'   => 'nullable',
            //
            'total'          => 'required|int',
            'admin_kredit'   => 'required|int',
            'admin_debit'    => 'required|int',
            'jumlah_netto'   => 'required|int',
            'selisih'        => 'nullable|int',
        ];
    }

    public function attributes(): array
    {
        return [
            'loket_id'       => 'Loket',
            'kasir_id'       => 'Kasir',
            'no_closingkasir' => 'No Closing Kasir',
            'tgl_closingkasir' => 'Tgl Closing Kasir',
            'no_buktibayar'  => 'No Bukti Bayar',
            'tgl_buktibayar' => 'Tgl Bukti Bayar',
            'no_pendaftaran'  => 'No Dokumen',
            'tgl_pelayanan'  => 'Tgl Dokumen',
            'status_id'     => 'Status',
            'klasifikasi'    => 'Klasifikasi',
            'uraian'         => 'Uraian',
            //
            'cara_pembayaran' => 'Cara Pembayaran',
            'bank_tujuan'    => 'Bank',
            'instalasi_id'   => 'Instalasi',
            'jenis_tagihan'  => 'Sumber Transaksi',
            //
            'pasien_nama'    => 'Pasien',
            'no_rekam_medik' => 'No Rekam Medik',
            'tgl_pendaftaran' => 'Tgl Pendaftaran',
            'carabayar_id'   => 'Cara Bayar',
            'penjamin_id'   => 'Penjamin',
            //
            'total'          => 'Jumlah',
            'admin_kredit'   => 'Biaya Admin EDC',
            'admin_debit'    => 'Biaya Admin QRIS',
            'jumlah_netto'   => 'Jumlah Netto',
            'selisih'        => 'Selisih',
        ];
    }
}
