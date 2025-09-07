<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Boolean;

class BillingSwaResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'noBayar' => (string) $this->no_bayar,
            'tglBayar' => $this->tgl_bayar ? date('d/m/Y', strtotime($this->tgl_bayar)) : null,
            'penyetor' => (string) $this->pihak3,
            'pasien' => (string) $this->pasien_nama,
            'uraian' => (string) $this->uraian,
            'noDokumen' => (string) $this->no_dokumen,
            'tglDokumen' => $this->tgl_dokumen ? date('d/m/Y', strtotime($this->tgl_dokumen)) : null,
            'sumberTransaksi' => (string) $this->sumber_transaksi,
            'metodeBayar' => (string) $this->metode_pembayaran,
            'caraPembayaran' => (string) $this->cara_pembayaran,
            'jumlahBruto' => (string) $this->total,
            'bank' => (string) $this->bank_tujuan,
            'biayaAdminEdc' => (string) $this->admin_kredit,
            'biayaAdminQris' => (string) $this->admin_debit,
            'instalasi' => (string) $this->instalasi_nama,
            'rekeningDpa' => (string) $this->rek_dpa,
            'selisih' => (string) $this->selisih,
            'jumlahNetto' => (int) $this->jumlah_netto,
        ];
    }
}
