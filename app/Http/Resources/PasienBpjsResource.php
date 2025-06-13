<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PasienBpjsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pendaftaran_id' => (int) $this->pendaftaran_id,
            'no_pendaftaran' => (string) $this->no_pendaftaran,
            'tgl_pendaftaran' => (string) $this->tgl_pendaftaran,
            'pasien_id' => (int) $this->pasien_id,
            'no_rekam_medik' => (string) $this->no_rekam_medik,
            'pasien_nama' => (string) $this->pasien_nama,
            'pasien_alamat' => (string) $this->pasien_alamat,
            'jenis_tagihan' => (string) $this->jenis_tagihan,
            'tgl_krs' => (int) $this->tgl_krs,
            'tgl_pelayanan' => (string) $this->tgl_pelayanan,
            'carabayar_id' => (int) $this->carabayar_id,
            'carabayar_nama' => (string) $this->carabayar_nama,
            'penjamin_id' => (int) $this->penjamin_id,
            'penjamin_nama' => (string) $this->penjamin_nama,
            'instalasi_id' => (int) $this->instalasi_id,
            'instalasi_nama' => (string) $this->instalasi_nama,
            'sep_id' => (string) $this->sep_id,
            'no_sep' => (string) $this->no_sep,
            'tgl_sep' => (string) $this->tgl_sep,
            'tgl_finalkasir' => (string) $this->tgl_finalkasir,
            'kasir_id' => (int) $this->kasir_id,
            'kasir_nama' => (string) $this->kasir_nama,
            'loket_id' => (int) $this->loket_id,
            'loket_nama' => (string) $this->loket_nama,
            'no_pengajuan_klaim' => (string) $this->no_pengajuan_klaim,
            'tgl_pengajuan_klaim' => (string) $this->tgl_pengajuan_klaim,
            'nomor_jaminan' => (string) $this->nomor_jaminan,
            'total_tagihan' => (string) $this->total_tagihan,
            'total_jaminan' => (string) $this->total_jaminan,
            'status_verifikasi' => (string) $this->status_verifikasi,
            'bulan_mrs' => (string) $this->bulan_mrs,
            'bulan_krs' => (string) $this->bulan_krs,
            'bulan_pelayanan' => (string) $this->bulan_pelayanan,
            'total' => (int) $this->total
        ];
    }
}
