<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRekeningView extends Model
{
    protected $table = 'master_rekening_v';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [
        'rek_id',
        'rek_nama'
    ];
}
