<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBank extends Model
{
    protected $table = "master_bank";
    protected $keyType = 'string';
    protected $primaryKey = 'bank_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bank_id',
        'bank_nama',
        'is_aktif',
    ];

    protected $casts = [
        'bank_id' => 'string',
    ];
}
