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
            'id'                => $this->id,
            'loket_id'          => (int) $this->loket_id,
            'kasir_id'          => (int) $this->kasir_id,
            'no_closingkasir'   => $this->no_closingkasir,
            'tgl_closingkasir'  => date('d/m/Y', strtotime($this->tgl_closingkasir)),
            'no_buktibayar'     => $this->no_buktibayar,
            'tgl_buktibayar'    => date('d/m/Y', strtotime($this->tgl_buktibayar)),
            'no_pendaftaran'    => $this->no_pendaftaran,
            'tgl_pelayanan'     => date('d/m/Y', strtotime($this->tgl_pelayanan)),
            'status_id'         => (string) $this->status_id,
            'klasifikasi'       => $this->klasifikasi,
            'uraian'            => $this->uraian,
            //
            'cara_pembayaran'   => $this->cara_pembayaran,
            'bank_tujuan'       => $this->bank_tujuan,
            'instalasi_id'      => (string) $this->instalasi_id,
            'jenis_tagihan'     => $this->jenis_tagihan,
            //
            'pasien_nama'       => $this->pasien_nama,
            'no_rekam_medik'    => $this->no_rekam_medik,
            'tgl_pendaftaran'   => date('d/m/Y', strtotime($this->tgl_pendaftaran)),
            'carabayar_id'      => (string) $this->carabayar_id,
            'penjamin_id'       => (int) $this->penjamin_id,
            //
            'jumlah_setor'      => $this->jumlah_setor,
            'total'             => $this->total,
            'admin_kredit'      => $this->admin_kredit,
            'admin_debit'       => $this->admin_debit,
            'jumlah_netto'      => $this->jumlah_netto,
            'selisih'           => $this->selisih,
            //
            'rekening_koran'    => $this->rekeningKoran,
            'tervalidasi'      => (bool) $this->tervalidasi,
        ];
    }
}
