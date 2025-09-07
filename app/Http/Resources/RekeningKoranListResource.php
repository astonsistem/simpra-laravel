<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RekeningKoranListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->rc_id,
            'rc_id'     => $this->rc_id,
            'no_rc'     => $this->no_rc,
            'nominal'   => $this->debit > 0 ? $this->debit : $this->kredit,
            'tgl_rc'    => date('d/m/Y', strtotime($this->tgl_rc)),
            'bank'      => strtoupper($this->bank),
            'uraian'    => $this->uraian
        ];
    }
}
