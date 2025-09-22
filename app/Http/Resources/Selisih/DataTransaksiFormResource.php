<?php

namespace App\Http\Resources\Selisih;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataTransaksiFormResource extends JsonResource
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
            'jumlah'            => (string) $this->jumlah,
            'rekening_dpa'      => $this->rekeningDpa ? [
                'rek_id'     => (string) $this->rekeningDpa->rek_id,
                'rek_nama'   => (string) $this->rekeningDpa->rek_nama,
            ] : null,
        ];
    }
}
