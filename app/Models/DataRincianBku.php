<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRincianBku extends Model
{
    protected $table = "data_rincian_bku";
    protected $primaryKey = 'rincian_id';
    public $timestamps = false;

    protected $fillable = [
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
        'is_web_change',
    ];

    protected $with = [
        'akun',
    ];

    protected $appends = [
        'akun_nama',
    ];

    public function akun()
    {
        return $this->belongsTo(MasterAkun::class, 'akun_id', 'akun_id');
    }
    public function getAkunNamaAttribute()
    {
        return $this->akun?->akun_nama;
    }
}
