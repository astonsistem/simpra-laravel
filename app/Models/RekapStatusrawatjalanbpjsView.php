<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapStatusrawatjalanbpjsView extends Model
{
    protected $table = 'rekap_statusrawatjalanbpjs_v';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [
        'total',
        'bulan_mrs',
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
