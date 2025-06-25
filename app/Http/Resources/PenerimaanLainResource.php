<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanLainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'no_dokumen' => (string) $this->no_dokumen,
            'tgl_dokumen' => (string) $this->tgl_dokumen,
            'akun_id' => (string) $this->akun_id,
            'pihak3' => (string) $this->pihak3,
            'pihak3_alamat' => (string) $this->pihak3_alamat,
            'pihak3_telp' => (string) $this->pihak3_telp,
            'uraian' => (string) $this->uraian,
            'tgl_bayar' => (string) $this->tgl_bayar,
            'no_bayar' => (string) $this->no_bayar,
            'sumber_transaksi' => (string) $this->sumber_transaksi,
            'transaksi_id' => (string) $this->transaksi_id,
            'metode_pembayaran' => (string) $this->metode_pembayaran,
            'total' => (string) $this->total,
            'pendapatan' => (string) $this->pendapatan,
            'pdd' => (string) $this->pdd,
            'piutang' => (string) $this->piutang,
            'cara_pembayaran' => (string) $this->cara_pembayaran,
            'bank_tujuan' => (string) $this->bank_tujuan,
            'admin_kredit' => (string) $this->admin_kredit,
            'admin_debit' => (string) $this->admin_debit,
            'kartubank' => (string) $this->kartubank,
            'no_kartubank' => (string) $this->no_kartubank,
            'rc_id' => $this->rc_id,
            'selisih' => (int) $this->selisih,
            'jumlah_netto' => (int) $this->jumlah_netto,
            'desc_piutang_pelayanan' => (string) $this->desc_piutang_pelayanan,
            'desc_piutang_lain' => (string) $this->desc_piutang_lain,
            'piutang_id' => (string) $this->piutang_id,
            'piutanglain_id' => (string) $this->piutanglain_id,
            'akun_data' => [
                'akun_id' => $this->whenLoaded('masterAkun', function () {
                    return $this->masterAkun->akun_id;
                }),
                'akun_kode' => $this->whenLoaded('masterAkun', function () {
                    return $this->masterAkun->akun_kode;
                }),
                'akun_nama' => $this->whenLoaded('masterAkun', function () {
                    return $this->masterAkun->akun_nama;
                }),
            ],
            'is_web_change' => (bool) $this->is_web_change,
        ];
    }
}
