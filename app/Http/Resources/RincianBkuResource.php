<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RincianBkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rincian_id'    => (int) $this->rincian_id,
            'bku_id'        => (int) $this->bku_id,
            'ket'           => (string) $this->ket,
            'uraian'        => (string) $this->uraian,
            'akun_id'       => (int) $this->akun_id,
            'rek_id'        => (int) $this->rek_id,
            'jumlah'        => (int) $this->jumlah,
            'pendapatan'    => (int) $this->pendapatan,
            'pdd'           => (int) $this->pdd,
            'piutang'       => (int) $this->piutang,
            'pad_rinci'     => (int) $this->pad_rinci
        ];
    }
}
