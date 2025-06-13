<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'username' => (string) $this->username,
            'nama' => (string) $this->nama,
            'nip' => (string) $this->nip,
            'email' => (string) $this->email,
            'no_telp' => (string) $this->no_telp,
            'jabatan' => (string) $this->jabatan,
            'role' => (string) $this->role,
        ];
    }
}
