<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaraPembayaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bayar_id' => (string) $this->bayar_id,
            'bayar_nama' => (string) $this->bayar_nama,
        ];
    }
}
