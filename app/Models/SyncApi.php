<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncApi extends Model
{
    protected $table = "master_sinkronisasi";

    protected $fillable = [
        'id',
        'sinkronisasi_id',
        'sinkronisasi_nama',
        'sinkronisasi_menu',
        'sinkronisasi_api',
        'sinkronisasi_groupuser',
        'sinkronisasi_status',
        'sinkronisasi_param'
    ];
}
