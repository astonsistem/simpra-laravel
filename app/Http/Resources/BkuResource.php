<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "bku_id" => $this->bku_id,
            "tgl" => $this->tgl,
            "ket" => $this->ket,
            "no_bku" => $this->no_bku,
            "tgl_bku" => $this->tgl_bku,
            "tgl_valid" => $this->tgl_valid,
            "jenis" => $this->jenis,
            "pad_id" => $this->pad_id,
            "pad_tgl" => $this->pad_tgl,
            "uraian" => $this->uraian,
            "nourut_bku" => $this->nourut_bku,
            "jumlah" => $this->jumlah,
            "pendapatan" => $this->pendapatan,
            "pdd" => $this->pdd,
            "piutang" => $this->piutang,
            "is_web_change" => $this->is_web_change,
            "rincian" => []
        ];
    }
}
