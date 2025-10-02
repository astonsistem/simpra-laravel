<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataBku extends Model
{
    protected $table = "data_bku";
    protected $primaryKey = "bku_id";
    public $timestamps = false;

    protected $fillable = [
        'tgl',
        'ket',
        'no_bku',
        'tgl_bku',
        'tgl_valid',
        'jenis',
        'pad_id',
        'pad_tgl',
        'uraian',
        'nourut_bku',
        'is_web_change'
    ];

    protected $with = [
        'rincian',
        'jenisBku',
    ];

    protected $appends = [
        'status',
        'total',
        'pendapatan',
        'pdd',
        'piutang',
        'jenisbku_id',
        'jenisbku_nama',
    ];

    public function getStatusAttribute()
    {
        if ($this->pad_id) {
            return 'PAD';
        }
        if ($this->tgl_valid) {
            return 'Validasi';
        }
        return 'Data Awal';
    }

    public function rincian()
    {
        //
        return $this->hasMany(DataRincianBku::class, 'bku_id', 'bku_id')->orderBy('rincian_id');
    }
    public function getTotalAttribute()
    {
        return $this->rincian->sum('jumlah');
    }
    public function getPendapatanAttribute()
    {
        return $this->rincian->sum('pendapatan');
    }
    public function getPddAttribute()
    {
        return $this->rincian->sum('pdd');
    }
    public function getPiutangAttribute()
    {
        return $this->rincian->sum('piutang');
    }

    public function jenisBku()
    {
        return $this->belongsTo(MasterJenisBku::class, 'jenis', 'jenisbku_id');
    }
    public function getJenisbkuIdAttribute()
    {
        return $this->jenisBku->jenisbku_id;
    }
    public function getJenisbkuNamaAttribute()
    {
        return $this->jenisBku->jenisbku_nama;
    }
}
