<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaraBayarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'carabayar_id' => (string) $this->carabayar_id,
            'carabayar_nama' => (string) $this->carabayar_nama,
        ];
    }
}
