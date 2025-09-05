<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingKasirFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'no_buktibayar'         => $this->no_buktibayar,
            'tgl_buktibayar'        => date('d/m/Y', strtotime($this->tgl_buktibayar)),
            'total'                 => $this->total,
            'admin_kredit'          => $this->admin_kredit,
            'admin_debit'           => $this->admin_debit,
            'selisih'               => $this->selisih,
            'jumlah_netto'          => $this->jumlah_netto,
            'cara_pembayaran'       => $this->cara_pembayaran,
            'bank_tujuan'           => $this->bank_tujuan,
            'no_kartubank_pasien'   => $this->no_kartubank_pasien,
            'kartubank_pasien'      => $this->kartubank_pasien,
            //
            'loket_id'              => (int) $this->loket_id,
            'kasir_id'              => (int) $this->kasir_id,
            'no_closingkasir'       => $this->no_closingkasir,
            'tgl_closingkasir'      => date('d/m/Y', strtotime($this->tgl_closingkasir)),
            'tgl_pendaftaran'       => date('d/m/Y', strtotime($this->tgl_pendaftaran)),
            'no_pendaftaran'        => $this->no_pendaftaran,
            'tgl_krs'               => date('d/m/Y', strtotime($this->tgl_krs)),
            'tgl_pelayanan'         => date('d/m/Y', strtotime($this->tgl_pelayanan)),
            'pasien_nama'           => $this->pasien_nama,
            'no_rekam_medik'        => $this->no_rekam_medik,
            'pasien_alamat'         => $this->pasien_alamat,
            //
            'instalasi_id'          => (string) $this->instalasi_id,
            'jenis_tagihan'         => $this->jenis_tagihan,
            'carabayar_id'          => (string) $this->carabayar_id,
            'penjamin_id'           => (int) $this->penjamin_id,
            'status_id'             => (string) $this->status_id,
            'klasifikasi'           => $this->klasifikasi,
            'rc_id'                 => (int) $this->rc_id,
            'rek_id'                => (int) $this->rek_id,
            //
            'rekening_koran'        => $this->rekeningKoran,
            'tervalidasi'           => (bool) $this->tervalidasi,
        ];
    }
}
