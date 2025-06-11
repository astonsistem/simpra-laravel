<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstalasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'instalasi_id' => (string) $this->instalasi_id,
            'instalasi_nama' => (string) $this->instalasi_nama,
        ];
    }
}
