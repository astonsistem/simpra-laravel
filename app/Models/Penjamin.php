<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjamin extends Model
{
    protected $table = "master_penjamin";

    protected $fillable = [
        'id',
        'penjamin_id',
        'penjamin_nama',
        'carabayar_id'
    ];
}
