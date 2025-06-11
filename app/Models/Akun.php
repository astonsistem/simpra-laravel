<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Akun extends Model
{
    protected $table = "master_akun";
    protected $keyType = 'string';
    public $incrementing = false;

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

    protected $casts = [
        'id' => 'string',
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
