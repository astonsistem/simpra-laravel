<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPendapatan extends Model
{
    protected $table = "data_anggaran_pendapatan";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'tgl',
        'tahun',
        'kategori_nama',
        'kategori_id',
        'rek_nama',
        'rek_id',
        'jumlah',
    ];
}
