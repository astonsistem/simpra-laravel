<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RekeningKoranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rc_id' => (int) $this->rc_id,
            'tgl' => (string) $this->tgl,
            'ket' => (string) $this->ket,
            'no_rc' => (string) $this->no_rc,
            'tgl_rc' => $this->tgl_rc ? date('d/m/Y', strtotime($this->tgl_rc)) : null,
            'rek_dari' => (string) $this->rek_dari,
            'nama_dari' => (string) $this->nama_dari,
            'akun_id' => (int) $this->akun_id,
            'akunls_id' => (int) $this->akunls_id,
            'uraian' => (string) $this->uraian,
            'bku_id' => (int) $this->bku_id,
            'no_bku' => (string) $this->no_bku,
            'ket_bku' => (string) $this->ket_bku,
            'klarif_lain' => (int) $this->klarif_lain,
            'klarif_layanan' => (int) $this->klarif_layanan,
            'debit' => (int) $this->debit,
            'kredit' => (int) $this->kredit,
            'nominal' => $this->debit > 0 ? $this->debit : $this->kredit,
            'klarif_admin' => (int) $this->klarif_admin,
            'kunci' => (int) $this->kunci,
            'pb' => (int) $this->pb,
            'mutasi' => $this->mutasi,
            'bank' => (string) $this->bank,
            'pb_dari' => (string) $this->pb_dari,
            'file_upload' => (string) $this->file_upload,
            'sync_at' => (string) $this->sync_at,
            'akun_data' => [
                'akun_id' => (string) $this->akun_id,
                'akun_kode' => (string) $this->akun_kode,
                'akun_nama' => (string) $this->akun_nama
            ],
            'akunls_data' => [
                'akun_id' => (string) $this->akun_id,
                'akun_kode' => (string) $this->akun_kode,
                'akun_nama' => (string) $this->akun_nama
            ],
            'status' => (string) $this->status,
            'is_web_change' => (string) $this->is_web_change,
        ];
    }
}
