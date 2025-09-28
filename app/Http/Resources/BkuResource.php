<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bku_id'        => (int) $this->bku_id,
            'tgl'           => (string) $this->tgl,
            'ket'           => (string) $this->ket,
            'no_bku'        => (string) $this->no_bku,
            'tgl_bku'       => (string) $this->tgl_bku,
            'tgl_valid'     => (string) $this->tgl_valid,
            'jenis'         => (int) $this->jenis,
            'jenisbku_id'   => (int) $this->jenisbku_id,
            'jenisbku_nama' => (string) $this->jenisbku_nama,
            'pad_id'        => (int) $this->pad_id,
            'pad_tgl'       => (string) $this->pad_tgl,
            'uraian'        => (string) $this->uraian,
            'nourut_bku'    => (int) $this->nourut_bku,
            'total'         => (int) $this->total,
            'pendapatan'    => (int) $this->pendapatan,
            'pdd'           => (int) $this->pdd,
            'piutang'       => (int) $this->piutang,
            'status'        => (string) $this->status,
            'rincian'       => RincianBKUResource::collection($this->rincian)
        ];
    }
}
