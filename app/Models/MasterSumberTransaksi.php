<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSumberTransaksi extends Model
{
    protected $table = "master_sumbertransaksi";
    protected $keyType = 'string';
    protected $primaryKey = 'sumber_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'sumber_id',
        'sumber_nama',
        'sumber_jenis',
    ];

    protected $casts = [
        'sumber_id' => 'string',
    ];
}
