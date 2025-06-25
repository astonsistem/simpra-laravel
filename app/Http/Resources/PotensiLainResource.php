<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PotensiLainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'tgl' => (string) $this->tgl,
            'ket' => (string) $this->ket,
            'no_dokumen' => (string) $this->no_dokumen,
            'tgl_dokumen' => (string) $this->tgl_dokumen,
            'akun_id' => (int) $this->akun_id,
            'pihak3' => (string) $this->pihak3,
            'pihak3_alamat' => (string) $this->pihak3_alamat,
            'pihak3_telp' => (string) $this->pihak3_telp,
            'uraian' => (string) $this->uraian,
            'tgl_berlaku' => (string) $this->tgl_berlaku,
            'tgl_akhir' => (string) $this->tgl_akhir,
            'jatuh_tempo' => (int) $this->jatuh_tempo,
            'besaran_per_satuan' => (int) $this->besaran_per_satuan,
            'total' => (int) $this->total,
            'total_pdd' => (int) $this->total_pdd,
            'total_piutang' => (int) $this->total_piutang,
            'reklas_pdd' => (int) $this->reklas_pdd,
            'pembayaran_piutang' => (int) $this->pembayaran_piutang,
            'monev_id' => (int) $this->monev_id ?? null,
            'terbayar' => (string) $this->terbayar,
            'is_web_change' => (bool) $this->is_web_change
        ];
    }
}
