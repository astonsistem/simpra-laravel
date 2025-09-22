<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuktiSetorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rc_id'          => (int) $this->rc_id,
            'tgl'            => (string) $this->tgl,
            'ket'            => (string) $this->ket,
            'no_rc'          => (string) $this->no_rc,
            'tgl_rc'         => (string) $this->tgl_rc,
            'rek_dari'       => (string) $this->rek_dari,
            'nama_dari'      => (string) $this->nama_dari,
            'akun_id'        => (int) $this->akun_id,
            'akunls_id'      => (int) $this->akunls_id,
            'uraian'         => (string) $this->uraian,
            'bku_id'         => (int) $this->bku_id,
            'no_bku'         => (string) $this->no_bku,
            'ket_bku'        => (string) $this->ket_bku,
            'klarif_lain'    => (int) $this->klarif_lain,
            'klarif_layanan' => (int) $this->klarif_layanan,
            'debit'          => (int) $this->debit,
            'kredit'         => (int) $this->kredit,
            'klarif_admin'   => (int) $this->klarif_admin,
            'kunci'          => (int) $this->kunci,
            'pb'             => (int) $this->pb,
            'mutasi'         => $this->mutasi,
            'bank'           => (string) $this->bank,
            'pb_dari'        => (string) $this->pb_dari,
            'file_upload'    => (string) $this->file_upload,
            'status'         => (string) $this->status,
            'selisih'        => (int) $this->selisih,
            'rek_id'         => (string) $this->rec_id,
            'volume'         => (int) $this->volume,
            'total_kwitansi' => (int) $this->total_kwitansi,
            'admin_kredit'   => (int) $this->admin_kredit,
            'admin_debit'    => (int) $this->admin_debit,
            'selisih'        => (int) $this->selisih,
            'total_setor'    => (int) $this->total_setor
        ];
    }
}
