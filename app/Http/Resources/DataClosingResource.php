<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataClosingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'closing_id' => (int) $this->closing_id,
            'tgl_closing' => (string) $this->tgl_closing,
            'no_closing' => (string) $this->no_closing,
            'vol_tunai' => (int) $this->vol_tunai,
            'vol_transfer_jatim' => (int) $this->vol_transfer_jatim,
            'vol_transfer_mandiri' => (int) $this->vol_transfer_mandiri,
            'vol_transfer_bca' => (int) $this->vol_transfer_bca,
            'vol_transfer_lainnya' => (int) $this->vol_transfer_lainnya,
            'vol_atm_jatim' => (int) $this->vol_atm_jatim,
            'vol_atm_mandiri' => (int) $this->vol_atm_mandiri,
            'vol_atm_bca' => (int) $this->vol_atm_bca,
            'vol_atm_lainnya' => (int) $this->vol_atm_lainnya,
            'vol_edc' => (int) $this->vol_edc,
            'vol_qris' => (int) $this->vol_qris,
            'vol_mb' => (int) $this->vol_mb,
            'vol_ecomm' => (int) $this->vol_ecomm,
            'vol_ue' => (int) $this->vol_ue,
            'tunai' => (int) $this->tunai,
            'transfer_jatim' => (int) $this->transfer_jatim,
            'transfer_mandiri' => (int) $this->transfer_mandiri,
            'transfer_bca' => (int) $this->transfer_bca,
            'transfer_lainnya' => (int) $this->transfer_lainnya,
            'atm_jatim' => (int) $this->atm_jatim,
            'atm_mandiri' => (int) $this->atm_mandiri,
            'atm_bca' => (int) $this->atm_bca,
            'atm_lainnya' => (int) $this->atm_lainnya,
            'edc' => (int) $this->edc,
            'qris' => (int) $this->qris,
            'mb' => (int) $this->mb,
            'ecomm' => (int) $this->ecomm,
            'ue' => (int) $this->ue,
            'rc_id' => (int) $this->rc_id,
            'kasir_id' => (int) $this->kasir_id,
            'kasir_nama' => (string) $this->kasir_nama,
            'penyetor_id' => (int) $this->penyetor_id,
            'penyetor_nama' => (string) $this->penyetor_nama,
            'is_web_change' => $this->is_web_change,
            'keterangan' => (string) $this->keterangan,
        ];
    }
}
