<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenisBku extends Model
{
    protected $table = "master_jenisbku";
    protected $primaryKey = 'jenisbku_id';
    public $timestamps = false;

    protected $fillable = [
        'jenisbku_id',
        'jenisbku_nama',
        'bku_kasdari',
        'bku_kaske',
    ];
}
