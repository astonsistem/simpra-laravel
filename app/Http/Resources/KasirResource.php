<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KasirResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'kasir_id' => (string) $this->kasir_id,
            'kasir_nama' => (string) $this->kasir_nama,
        ];
    }
}
