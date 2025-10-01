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
            'akun_data' => $this->whenLoaded('akunData', function () {
                return [
                    'akun_id' => $this->akunData->akun_id,
                    'akun_kode' => $this->akunData->akun_kode,
                    'akun_nama' => $this->akunData->akun_nama
                ];
            }),
            'akunls_data' => $this->whenLoaded('akunlsData', function () {
                return [
                    'akunls_id' => $this->akunlsData->akun_id,
                    'akunls_kode' => $this->akunlsData->akun_kode,
                    'akunls_nama' => $this->akunlsData->akun_nama
                ];
            }),
            'terklarifikasi' => (int) ($this->klarif_layanan + $this->klarif_lain),
            'belum_terklarifikasi' => (int) (($this->debit > 0 ? $this->debit : $this->kredit) - ($this->klarif_layanan + $this->klarif_lain)),
            'rekening_dpa' => $this->whenLoaded('rekeningDpa', function () {
                return [
                    'rek_id' => $this->rekeningDpa->rek_id ?? null,
                    'rek_nama' => $this->rekeningDpa->rek_nama ?? null
                ];
            }),
            'status' => (string) $this->status,
            'is_web_change' => (string) $this->is_web_change,
        ];
    }
}
