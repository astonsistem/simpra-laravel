<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataSelisihResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'tglBukti' => (string) $this->tgl_buktibayar,
            'noBukti' => (string) $this->no_buktibayar,
            'nominal' => (string) $this->jumlah,
            'loketKasir' => (string) $this->loket_nama,
            'kasirNama' => (string) $this->kasir_nama,
            'penyetor' => (string) $this->penyetor,
            'tersetor' => (string) $this->tersetor,
            'sumberTransaksi' => (string) $this->sumber_transaksi,
            'rekeningDpa' => (string) $this->rek_id,
            'jenis' => (string) $this->jenis,
            'klasifikasi' => (string) $this->klasifikasi,
        ];
    }
}
