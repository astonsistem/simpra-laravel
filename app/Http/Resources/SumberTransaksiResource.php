<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SumberTransaksiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sumber_id' => (string) $this->sumber_id,
            'sumber_nama' => (string) $this->sumber_nama,
            'sumber_jenis' => (string) $this->sumber_jenis,
        ];
    }
}
