<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instalasi extends Model
{
    protected $table = "master_instalasi";

    protected $fillable = [
        'id',
        'instalasi_id',
        'instalasi_nama',
    ];
}
