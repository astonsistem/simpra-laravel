<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class PenerimaanLainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'akun_id'                   => $this->akun_id,
            'akun_nama'                 => $this->masterAkun ? $this->masterAkun->akun_nama : null,
            'admin_debit'               => (int) $this->admin_debit,
            'admin_kredit'              => (int) $this->admin_kredit,
            'bank_tujuan'               => $this->bank_tujuan,
            'cara_pembayaran'           => \App\Models\MasterCaraPembayaran::getValueFromLabel($this->cara_pembayaran),
            'desc_piutang_lain'         => $this->desc_piutang_lain,
            'desc_piutang_pelayanan'    => $this->desc_piutang_pelayanan,
            'is_valid'                  => (bool) $this->is_valid,
            'jumlah_netto'              => (int) $this->getJumlahNetto(),
            'jumlah_bruto'              => (int) $this->total,
            'metode_pembayaran'         => $this->metode_pembayaran,
            'no_bayar'                  => $this->no_bayar,
            'no_dokumen'                => $this->no_dokumen,
            'pdd'                       => (int) $this->pdd,
            'pendapatan'                => (int) $this->getPendapatan(),
            'pihak3'                    => $this->pihak3,
            'piutang'                   => (int) $this->getPiutang(),
            'piutang_id'                => $this->piutang_id,
            'rc_id'                     => $this->rc_id,
            'rek_id'                    => $this->rek_id,
            'selisih'                   => (int) $this->selisih,
            'sumber_transaksi'          => $this->sumber_transaksi,
            'sumber'                    => !empty($this->sumber) ? [
                                            'sumber_id' => $this->sumber->sumber_id,
                                            'sumber_nama' => $this->sumber->sumber_nama,
                                            'sumber_jenis' => $this->sumber->sumber_jenis,
                                        ] : null,
            'tgl_bayar'                 => $this->tgl_bayar,
            'tgl_dokumen'               => $this->tgl_dokumen,
            'total'                     => (int) $this->total,
            'uraian'                    => $this->uraian,
            'total_jumlah_netto'        => (int) $this->jumlah_netto > 0 ? $this->jumlah_netto : $this->total_jumlah_netto,
            'rekening_dpa'               => $this->rekeningDpa,
        ];
    }

    private function getJumlahNetto()
    {
        return $this->total - $this->admin_kredit - $this->admin_debit + $this->selisih;
    }

    /**
     * Pendapatan default jika piutang_id is null maka = Jumlah Netto,
     * jika piutang_id terisi default = 0
     */
    private function getPendapatan()
    {
        if(empty($this->piutang_id) || is_null($this->piutang_id)) {
            return $this->getJumlahNetto();
        }

        return 0;
    }

    /**
     * Piutang default = jika piutang_id terisi maka = Jumlah Netto,
     * jika tidak default = 0
     */
    private function getPiutang()
    {
        if(!empty($this->piutang_id)) {
            return $this->getJumlahNetto();
        }

        return 0;

    }
}
