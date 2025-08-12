<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanSelisihResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'noBukti' => (string) $this->no_buktibayar,
            'tglBukti' => (string) $this->tgl_buktibayar,
            'tglSetor' => (string) $this->tgl_setor,
            'noSetor' => (string) $this->tandabuktibayar_id,
            'nominal' => (string) $this->jumlah,
            'rekeningDpa' => (string) $this->rek_id,
            'loketKasir' => (string) $this->loket_nama,
            'caraPembayaran' => (string) $this->cara_pembayaran,
            'bank' => (string) $this->bank_tujuan,
            'jenis' => (string) $this->jenis,
        ];
    }
}
