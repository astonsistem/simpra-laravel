<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingSwaSimpleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => (string) $this->id,
            'admin_debit'           => $this->admin_debit,
            'admin_kredit'          => $this->admin_kredit,
            'akun_id'               => $this->akun_id,
            'bank_tujuan'           => $this->bank_tujuan,
            'cara_pembayaran'       => $this->cara_pembayaran,
            'jumlah_netto'          => $this->jumlah_netto,
            'no_bayar'              => $this->no_bayar,
            'no_dokumen'            => $this->no_dokumen,
            'pdd'                   => $this->pdd,
            'pendapatan'            => $this->pendapatan,
            'pihak3'                => $this->pihak3,
            'piutang'               => $this->piutang,
            'rek_id'                => $this->rek_id,
            'selisih'               => $this->selisih,
            'sumber_transaksi'      => $this->sumber_transaksi,
            'tgl_bayar'             => $this->tgl_bayar ? date('d/m/Y', strtotime($this->tgl_bayar)) : null,
            'tgl_dokumen'           => $this->tgl_dokumen ? date('d/m/Y', strtotime($this->tgl_dokumen)) : null,
            'total'                 => $this->total,
            'uraian'                => $this->uraian

        ];
    }
}
