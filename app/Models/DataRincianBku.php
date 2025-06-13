<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRincianBku extends Model
{
    protected $table = "data_rincian_bku";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rincian_id',
        'bku_id',
        'ket',
        'uraian',
        'akun_id',
        'rek_id',
        'jumlah',
        'pendapatan',
        'pdd',
        'piutang',
        'pad_rinci',
        'no_bku',
        'is_web_change',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
