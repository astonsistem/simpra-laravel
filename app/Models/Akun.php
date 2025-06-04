<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Akun extends Model
{
    protected $table = "master_akun";

    protected $fillable = [
        'id',
        'akun_id',
        'akun_kode',
        'akun_nama',
        'rek_id',
        'rek_nama',
        'akun_kelompok',
        'created_at'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
