<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarikPotensiPelayanan extends Model
{
    protected $table = "tarik_potensilayanan_v";

    protected $fillable = [
        'id',
        'penjamin_id',
        'penjamin_nama',
        'tahun',
        'bulan',
        'jumlah',
        'diterima',
        'ditolak',
        'vol',
        'vol_terima',
        'vol_tolak',
        'carabayar_id',
        'akun_id',
        'rincian_id',
        'sumber_pembiayaan'
    ];
}
