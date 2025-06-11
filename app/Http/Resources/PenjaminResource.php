<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenjaminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'penjamin_id' => (string) $this->penjamin_id,
            'penjamin_nama' => (string) $this->penjamin_nama,
        ];
    }
}
