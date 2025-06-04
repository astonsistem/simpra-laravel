<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bku extends Model
{
    protected $table = "data_bku";

    protected $fillable = [
        'bku_id',
        'tgl',
        'ket',
        'no_bku',
        'tgl_bku',
        'tgl_valid',
        'jenis',
        'pad_id',
        'pad_tgl',
        'uraian',
        'nourut_bku',
        'is_web_change'
    ];
}
