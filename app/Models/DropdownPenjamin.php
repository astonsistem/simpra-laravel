<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DropdownPenjamin extends Model
{
    protected $table = "master_penjamin_v";
    protected $primaryKey = 'penjamin_id';
    public $timestamps = false;

    protected $fillable = [
        'penjamin_id',
        'penjamin_nama',
        'carabayar_id'
    ];
}
