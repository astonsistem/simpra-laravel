<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanSelisihSimpleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'tgl_bukti'             => $this->tgl_bukti ? date('/d/m/Y', strtotime($this->tgl_bukti)) : null,
            'tgl_buktibayar'        => $this->tgl_buktibayar ? date('d/m/Y', strtotime($this->tgl_buktibayar)) : null,
            'tgl_setor'             => $this->tgl_setor ? date('d/m/Y', strtotime($this->tgl_setor)) : null,
            'total_jumlah_netto'    => (int) $this->jumlah_netto > 0 ? (int) $this->jumlah_netto : (int) $this->total_jumlah_netto,
        ]);
    }
}
