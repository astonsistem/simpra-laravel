<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRekapRegister extends Model
{
    protected $table = "data_rekap_register";
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'tgl',
        'total',
        'batal_periksa',
        'rj',
        'ri',
    ];
}
