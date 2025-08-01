<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCaraPembayaran extends Model
{
    protected $table = "master_carapembayaran";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bayar_id',
        'bayar_nama',
        'is_aktif',
    ];

    protected $casts = [
        'bayar_id' => 'string',
    ];
}
