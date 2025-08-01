<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaraBayar extends Model
{
    protected $table = "master_carabayar";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'carabayar_id',
        'carabayar_nama'
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
