<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TempPenerimaanSwaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'transaksi_id' => (int) $this->transaksi_id,
            'sumber_transaksi' => (string) $this->sumber_transaksi,
            'akun_id' => (int) $this->akun_id,
            'pihak3' => (string) $this->pihak3,
            'pihak3_alamat' => (string) $this->pihak3_alamat,
            'pihak3_telp' => (string) $this->pihak3_telp,
            'uraian' => (string) $this->uraian,
            'tgl_bayar' => (string) $this->tgl_bayar,
            'no_bayar' => (string) $this->no_bayar,
            'total' => (int) $this->total,
            'pendapatan' => (int) $this->pendapatan,
            'pdd' => (string) $this->pdd,
            'piutang' => (string) $this->piutang,
            'cara_pembayaran' => (string) $this->cara_pembayaran,
            'bank_tujuan' => (string) $this->bank_tujuan,
            'admin_kredit' => (int) $this->admin_kredit,
            'admin_debit' => (int) $this->admin_debit,
            'kartubank' => (string) $this->kartubank,
            'no_kartubank' => (string) $this->no_kartubank
        ];
    }
}
