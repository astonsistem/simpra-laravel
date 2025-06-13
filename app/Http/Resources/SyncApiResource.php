<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'sinkronisasi_id' => (string) $this->sinkronisasi_id,
            'sinkronisasi_nama' => (string) $this->sinkronisasi_nama,
            'sinkronisasi_menu' => (string) $this->sinkronisasi_menu,
            'sinkronisasi_status' => (string) $this->sinkronisasi_status,
            'sinkronisasi_param' => $this->sinkronisasi_param,
            'sinkronisasi_api' => (string) $this->sinkronisasi_api,
        ];
    }
}
