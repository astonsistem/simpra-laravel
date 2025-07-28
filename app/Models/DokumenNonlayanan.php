<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenNonlayanan extends Model
{
    protected $table = "dokumen_nonlayanan";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'tgl',
        'ket',
        'no_dokumen',
        'tgl_dokumen',
        'akun_id',
        'pihak3',
        'pihak3_alamat',
        'pihak3_telp',
        'uraian',
        'tgl_berlaku',
        'tgl_akhir',
        'jatuh_tempo',
        'besaran_per_satuan',
        'total',
        'total_pdd',
        'total_piutang',
        'reklas_pdd',
        'pembayaran_piutang',
        'monev_id',
        'is_web_change',
    ];
}
