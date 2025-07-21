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
            'noBayar' => (string) $this->no_bayar,
            'tglBayar' => (string) $this->tgl_bayar,
            'pasien' => (string) $this->pasien_nama,
            'uraian' => (string) $this->uraian,
            'noDokumen' => (string) $this->no_dokumen,
            'tglDokumen' => (string) $this->tgl_dokumen,
            'sumberTransaksi' => (string) $this->sumber_transaksi,
            'instalasi' => (string) $this->instalasi_nama,
            'metodeBayar' => (string) $this->metode_pembayaran,
            'caraBayar' => (string) $this->cara_pembayaran,
            'rekeningDpa' => (string) $this->rekening_dpa,
            'bank' => (string) $this->bank_tujuan,
            'jumlahBruto' => (string) $this->total,
            'biayaAdminEdc' => (string) $this->admin_kredit,
            'biayaAdminQris' => (string) $this->admin_debit,
            'selisih' => (string) $this->selisih,
            'jumlahNetto' => (int) $this->jumlah_netto,
        ];
    }
}
