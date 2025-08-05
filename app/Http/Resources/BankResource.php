<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bank_id' => (string) $this->bank_id,
            'bank_nama' => (string) $this->bank_nama,
        ];
    }
}
