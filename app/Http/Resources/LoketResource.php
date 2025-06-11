<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'loket_id' => (string) $this->loket_id,
            'loket_nama' => (string) $this->loket_nama,
        ];
    }
}
