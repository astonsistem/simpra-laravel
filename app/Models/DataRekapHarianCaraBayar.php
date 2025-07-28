<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRekapHarianCaraBayar extends Model
{
    protected $table = "data_rekap_harian_carabayar";
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'tgl_pelayanan',
        'carabayar_id',
        'carabayar_nama',
        'penjamin_id',
        'penjamin_nama',
        'jenis_tagihan',
        'nilaitagihan_klaim',
        'nilaitagihan_belumklaim',
        'nilai_klaim',
        'berkas_belumklaim',
    ];
}
