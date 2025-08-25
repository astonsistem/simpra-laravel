<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendapatanPenjamin1Resource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string) $this->id,
            'pelayanan_id'      => (string) $this->pelayanan_id,
            'pendaftaran_id'    => (int) $this->pendaftaran_id,
            'no_pendaftaran'    => (string) $this->no_pendaftaran,
            'tgl_pendaftaran'   => (string) $this->tgl_pendaftaran,
            'pasien_id'         => (int) $this->pasien_id,
            'jenis_tagihan'     => (string) $this->jenis_tagihan,
            'tgl_krs'           => (string) $this->tgl_krs,
            'tgl_pelayanan'     => (string) $this->tgl_pelayanan,
            'no_rekam_medik'    => (string) $this->no_rekam_medik,
            'pasien_nama'       => (string) $this->pasien_nama,
            'carabayar_id'      => (int) $this->carabayar_id,
            'carabayar_nama'    => (string) $this->carabayar_nama,
            'penjamin_id'       => (int) $this->penjamin_id,
            'penjamin_nama'     => (string) $this->penjamin_nama,
            'no_penjamin'       => (string) $this->no_penjamin,
            'tgl_jaminan'       => (string) $this->tgl_jaminan,
            'instalasi_id'      => (int) $this->instalasi_id,
            'instalasi_nama'    => (string) $this->instalasi_nama,
            'kasir_id'          => (int) $this->kasir_id,
            'kasir_nama'        => (string) $this->kasir_nama,
            'loket_id'          => (int) $this->loket_id,
            'loket_nama'        => (string) $this->loket_nama,
            'total_dijamin'     => (float) $this->total_dijamin,
            'bulan_mrs'         => (string) $this->bulan_mrs,
            'bulan_krs'         => (string) $this->bulan_krs,
            'bulan_pelayanan'   => (string) $this->bulan_pelayanan,
            'biaya_admin'       => (int) $this->biaya_admin,
            'status'            => (string) $this->status
        ];
    }
}
