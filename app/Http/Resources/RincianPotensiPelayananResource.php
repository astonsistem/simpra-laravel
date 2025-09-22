<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RincianPotensiPelayananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rincian_id'     => (int) $this->rincian_id,
            'piutang_id'     => (string) $this->piutang_id,
            'pendaftaran_id' => (int) $this->pendaftaran_id,
            'total_tagihan'  => (int) $this->total_tagihan,
            'total_klaim'    => (int) $this->total_klaim,
            'total_verif'    => (int) $this->total_verif,
            'total_bayar'    => (int) $this->total_bayar,
            'jenis'          => (string) $this->jenis,
            'bulan'          => (int) $this->bulan,
            'tahun'          => (int) $this->tahun,
            'penjamin_id'    => (int) $this->penjamin_id,
            'sumber'         => (string) $this->sumber,
            'is_web_change'  => (bool) $this->is_web_change,
            'sep'            => (string) $this->sep,
            'norm'           => (string) $this->norm,
            'nama'           => (string) $this->nama,
            'tgl_mrs'        => (string) $this->tgl_mrs,
        ];
    }
}
