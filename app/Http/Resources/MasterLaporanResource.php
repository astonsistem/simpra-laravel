<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterLaporanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'kode_laporan' => (string) $this->kode_laporan,
            'nama_laporan' => (string) $this->nama_laporan,
            'params' => $this->params,
        ];
    }
}
