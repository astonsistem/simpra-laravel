<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = "master_status";

    protected $fillable = [
        'id',
        'status_id',
        'status_nama',
        'status_ket',
        'status_app',
    ];
}
