<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendapatanPelayananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pendaftaran_id' => (int) $this->pendaftaran_id,
            'tgl_pendaftaran' => (string) $this->tgl_pendaftaran,
            'pasien_id' => (int)$this->pasien_id,
            'pasien_alamat' => (string) $this->pasien_alamat,
            'jenis_tagihan' => (string) $this->jenis_tagihan,
            'tgl_krs' => (string) $this->tgl_krs,
            'tgl_pelayanan' => (string) $this->tgl_pelayanan,
            'carabayar_id' => (int)$this->carabayar_id,
            'carabayar_nama' => (string) $this->carabayar_nama,
            'penjamin_id' => (int) $this->penjamin_id,
            'penjamin_nama' => (string) $this->penjamin_nama,
            'no_penjamin' => (string) $this->no_penjamin,
            'tgl_jaminan' => (string) $this->tgl_jaminan,
            'instalasi_id' => (int)$this->instalasi_id,
            'instalasi_nama' => (string) $this->instalasi_nama,
            'kasir_id' => (int)$this->kasir_id,
            'kasir_nama' => (string) $this->kasir_nama,
            'koreksi_sharing' => (int) $this->koreksi_sharing,
            'loket_id' => (int)$this->loket_id,
            'loket_nama' => (string) $this->loket_nama,
            'layanan' => (string) $this->layanan,
            'obatalkes' => (string) $this->obatalkes,
            'penunjang' => (string) $this->penunjang,
            'total' => (int) $this->total,
            'total_dijamin' => (int) $this->total_dijamin,
            'total_sharing' => (int) $this->total_sharing,
            'pendapatan' => (int) $this->pendapatan,
            'piutang' => (int) $this->piutang,
            'piutang_perorangan' => (int) $this->piutang_perorangan,
            'pdd' => (int) $this->pdd,
            'biaya_admin' => (int) $this->biaya_admin,
            'obat_dijamin' => (int) $this->obat_dijamin,
            'pembayaranpelayanan_id' => $this->pembayaranpelayanan_id,
            'status_id' => (int)$this->status_id,
            'bulan_mrs' => (string) $this->bulan_mrs,
            'bulan_krs' => (string) $this->bulan_krs,
            'bulan_pelayanan' => (string) $this->bulan_pelayanan,
            'no_pendaftaran' => (string) $this->no_pendaftaran,
            'no_rekam_medik' => (string) $this->no_rekam_medik,
            'pasien_nama' => (string) $this->pasien_nama,
            'status_fase1' => (string) $this->status_fase1,
            'status_fase2' => (string) $this->status_fase2,
            'status_fase3' => (string) $this->status_fase3,
            'is_valid' => $this->is_valid,
            'is_penjaminlebih1' => $this->is_penjaminlebih1,
            'is_naikkelas' => $this->is_naikkelas,
            'hak_kelasrawat' => (string) $this->hak_kelasrawat,
            'naik_kelasrawat' => (string) $this->naik_kelasrawat,
            'is_web_change' => $this->is_web_change
        ];
    }
}
