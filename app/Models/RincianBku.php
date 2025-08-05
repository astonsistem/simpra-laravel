<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RincianBku extends Model
{
    protected $table = "rincian_bku";
    protected $primaryKey = 'rincian_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

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
}
