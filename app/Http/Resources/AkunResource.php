<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AkunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'akun_id' => (string) $this->akun_id,
            'akun_kode' => (string) $this->akun_kode,
            'akun_nama' => (string) $this->akun_nama,
            'rek_id' => (string) $this->rek_id,
            'rek_nama' => (string) $this->rek_nama,
            'akun_kelompok' => (string) $this->akun_kelompok,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toIso8601String() : null,
        ];
    }
}
