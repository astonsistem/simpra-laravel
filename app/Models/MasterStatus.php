<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterStatus extends Model
{
    protected $table = "master_status";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'status_id',
        'status_nama',
        'status_ket',
        'status_app',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
