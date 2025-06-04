<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loket extends Model
{
    protected $table = "master_loket";

    protected $fillable = [
        'id',
        'loket_id',
        'loket_nama',
    ];
}
