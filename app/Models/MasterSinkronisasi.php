<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterSinkronisasi extends Model
{
    protected $table = "master_sinkronisasi";
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;

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

    protected $casts = [
        'id' => 'string',
        'sinkronisasi_param' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }
}
