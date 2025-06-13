<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataClosing extends Model
{
    protected $table = 'data_closing';
    protected $primaryKey = "closing_id";

    protected $fillable = [
        'closing_id',
        'tgl_closing',
        'no_closing',
        'vol_tunai',
        'vol_transfer_jatim',
        'vol_transfer_mandiri',
        'vol_transfer_bca',
        'vol_transfer_lainnya',
        'vol_atm_jatim',
        'vol_atm_mandiri',
        'vol_atm_bca',
        'vol_atm_lainnya',
        'vol_edc',
        'vol_qris',
        'vol_mb',
        'vol_ecomm',
        'vol_ue',
        'tunai',
        'transfer_jatim',
        'transfer_mandiri',
        'transfer_bca',
        'transfer_lainnya',
        'atm_jatim',
        'atm_mandiri',
        'atm_bca',
        'atm_lainnya',
        'edc',
        'qris',
        'mb',
        'ecomm',
        'ue',
        'rc_id',
        'kasir_id',
        'kasir_nama',
        'penyetor_id',
        'penyetor_nama',
        'is_web_change',
        'keterangan'
    ];
}
