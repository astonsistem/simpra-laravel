<?php

namespace App\Http\Resources\Selisih;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataTransaksiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string) $this->id,
            'tgl_setor'         => (string) $this->tgl_setor,
            'tgl_buktibayar'    => (string) $this->tgl_buktibayar,
            'no_buktibayar'     => (string) $this->no_buktibayar,
            'penyetor'          => (string) $this->penyetor,
            'jenis'             => (string) $this->jenis,
            'cara_pembayaran'   => (string) $this->cara_pembayaran,
            'bank_tujuan'       => (string) $this->bank_tujuan,
            'jumlah'            => $this->jumlah,
            'admin_kredit'      => (int) $this->admin_kredit ?? 0,
            'admin_debit'       => (int) $this->admin_debit ?? 0,
            'jumlah_netto'      => (int) $this->jumlah - (int) $this->admin_kredit,
            'sumber_transaksi'  => (string) $this->sumber_transaksi,
            'klasifikasi'       => (string) $this->klasifikasi,
            'rekening_dpa'      => $this->rekeningDpa ? [
                'rek_id'     => (string) $this->rekeningDpa->rek_id,
                'rek_nama'   => (string) $this->rekeningDpa->rek_nama,
            ] : null,
            'rc_id'             => (string) $this->rc_id,
            'is_valid'          => (bool) $this->is_valid
        ];
    }
}
