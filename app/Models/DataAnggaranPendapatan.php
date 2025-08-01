<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAnggaranPendapatan extends Model
{
    protected $table = "data_anggaran_pendapatan";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'tgl',
        'tahun',
        'kategori_utama',
        'kategori_id',
        'rek_nama',
        'rek_id',
        'jumlah'
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
