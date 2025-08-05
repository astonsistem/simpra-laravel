<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Instalasi extends Model
{
    protected $table = "master_instalasi";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'instalasi_id',
        'instalasi_nama',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
