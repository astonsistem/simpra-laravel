<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasir extends Model
{
    protected $table = "master_kasir";

    protected $fillable = [
        'id',
        'kasir_id',
        'kasir_nama',
    ];
}
