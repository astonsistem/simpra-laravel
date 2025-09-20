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
            'noBayar' => (string) $this->no_buktibayar,
            'tglBayar' => (string) date('d/m/Y', strtotime($this->tgl_buktibayar)),
            'pasien' => (string) $this->pasien_nama,
            'uraian' => (string) $this->uraian,
            'noDokumen' => (string) $this->no_pendaftaran,
            'tglDokumen' => (string) date('d/m/Y', strtotime($this->tgl_pelayanan)),
            'sumberTransaksi' => (string) $this->jenis_tagihan,
            'instalasi' => (string) $this->instalasi_nama,
            'metodeBayar' => (string) $this->metode_bayar,
            'caraBayarId' => (string) $this->carabayar_id,
            'caraBayar' => (string) $this->carabayar_nama,
            'bank' => (string) $this->bank_tujuan,
            'jumlahBruto' => (string) $this->total,
            'biayaAdminEdc' => (string) $this->admin_kredit,
            'biayaAdminQris' => (string) $this->admin_debit,
            'selisih' => (string) $this->selisih,
            'jumlahNetto' => (int) $this->jumlah_netto,
            'status' => (string) $this->status_name,
            'statusId' => (string) $this->status_id,
            'rcId' => (string) $this->rc_id,
            //
            'loketId' => (string) $this->loket_id,
            'loket' => (string) $this->loket_nama,
            'kasirId' => (string) $this->kasir_id,
            'kasir' => (string) $this->kasir_nama,
            'noClosingKasir' => (string) $this->no_closingkasir,
            'tglClosingKasir' => (string) date('d/m/Y', strtotime($this->tgl_closingkasir)),
            'klasifikasi' => (string) $this->klasifikasi,
            'rekeningDpa' => $this->rekeningDpa ? [
                'rekId' => (string) $this->rekeningDpa->rek_id,
                'rekNama' => (string) $this->rekeningDpa->rek_nama,
            ] : null,
        ];
    }
}
