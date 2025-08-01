<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapStatusrawatinapbpjsView extends Model
{
    protected $table = 'rekap_statusrawatinapbpjs_v';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [
        'total',
        'bulan_krs',
        'bulan_sep',
        'belum_upload',
        'belum_pengajuan',
        'pengajuan',
        'pending',
        'gagal',
        'tidak_layak',
        'terverif'
    ];
}
