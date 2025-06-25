<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PotensiLayananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'pendaftaran_id' => (int) $this->pendaftaran_id,
            'no_pendaftaran' => (string) $this->no_pendaftaran,
            'tgl_pendaftaran' => (string) $this->tgl_pendaftaran,
            'pasien_id' => (string) $this->pasien_id,
            'no_rekam_medik' => (string) $this->no_rekam_medik,
            'pasien_nama' => (string) $this->pasien_nama,
            'pasien_alamat' => (string) $this->pasien_alamat,
            'jenis_tagihan' => (string) $this->jenis_tagihan,
            'tgl_pelayanan' => (string) $this->tgl_pelayanan,
            'instalasi_id' => (string) $this->instalasi_id,
            'instalasi_nama' => (string) $this->instalasi_nama,
            'carabayar_id' => (int) $this->carabayar_id,
            'carabayar_nama' => (string) $this->carabayar_nama,
            'penjamin_id' => (int) $this->penjamin_id,
            'penjamin_nama' => (string) $this->penjamin_nama,
            'no_pengajuan' => (string) $this->no_pengajuan,
            'tgl_pengajuan' => (string) $this->tgl_pengajuan,
            'no_dokumen' => (string) $this->no_dokumen,
            'tgl_dokumen' => (string) $this->tgl_dokumen,
            'uraian' => (string) $this->uraian,
            'total_pengajuan' => (string) $this->total_pengajuan,
            'total' => (string) $this->total,
            'status_id' => (string) $this->status_id,
            'akun_id' => (string) $this->akun_id,
            'terbayar' => (int) $this->terbayar,
            'sisa_potensi' => (int) $this->sisa_potensi,
            'pelayanan_id' => (int) $this->pelayanan_id,
            'is_web_change' => (bool) $this->is_web_change
        ];
    }
}
