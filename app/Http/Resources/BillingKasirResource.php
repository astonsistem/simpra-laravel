<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingKasirResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'pendaftaran_id' => (int) $this->pendaftaran_id,
            'no_pendaftaran' => (string) $this->no_pendaftaran,
            'tgl_pendaftaran' => (string) $this->tgl_pendaftaran,
            'pasien_id' => (int) $this->pasien_id,
            'no_rekam_medik' => (string) $this->no_rekam_medik,
            'pasien_nama' => (string) $this->pasien_nama,
            'pasien_alamat' => (string) $this->pasien_alamat,
            'jenis_tagihan' => (string) $this->jenis_tagihan,
            'tgl_krs' => (string) $this->tgl_krs,
            'tgl_pelayanan' => (string) $this->tgl_pelayanan,
            'carabayar_id' => (int) $this->carabayar_id,
            'carabayar_nama' => (string) $this->carabayar_nama,
            'penjamin_id' => (int) $this->penjamin_id,
            'penjamin_nama' => (string) $this->penjamin_nama,
            'instalasi_id' => (int) $this->instalasi_id,
            'instalasi_nama' => (string) $this->instalasi_nama,
            'metode_bayar' => (string) $this->metode_bayar,
            'tandabuktibayar_id' => (int) $this->tandabuktibayar_id,
            'no_buktibayar' => (string) $this->no_buktibayar,
            'tgl_buktibayar' => (string) $this->tgl_buktibayar,
            'sep_id' => (string) $this->sep_id,
            'no_sep' => (string) $this->no_sep,
            'tgl_sep' => (string) $this->tgl_sep,
            'cara_pembayaran' => (string) $this->cara_pembayaran,
            'bank_tujuan' => (string) $this->bank_tujuan,
            'admin_kredit' => (string) $this->admin_kredit,
            'admin_debit' => (string) $this->admin_debit,
            'kartubank_pasien' => (string) $this->kartubank_pasien,
            'no_kartubank_pasien' => (string) $this->no_kartubank_pasien,
            'closingkasir_id' => (string) $this->closingkasir_id,
            'tgl_closingkasir' => (string) $this->tgl_closingkasir,
            'no_closingkasir' => (string) $this->no_closingkasir,
            'kasir_id' => (int) $this->kasir_id,
            'kasir_nama' => (string) $this->kasir_nama,
            'loket_id' => (int) $this->loket_id,
            'loket_nama' => (string) $this->loket_nama,
            'guna_bayar' => (string) $this->guna_bayar,
            'total' => (string) $this->total,
            'klasifikasi' => (string) $this->klasifikasi,
            'status_id' => (int) $this->status_id,
            'bulan_mrs' => (string) $this->bulan_mrs,
            'bulan_krs' => (string) $this->bulan_krs,
            'bulan_pelayanan' => (string) $this->bulan_pelayanan,
            'akun_id' => (int) $this->akun_id,
            'selisih' => (int) $this->selisih,
            'jumlah_netto' => (int) $this->jumlah_netto,
            'rc_id' => (int) $this->rc_id,
            'is_web_change' => $this->is_web_change,

        ];
    }
}
