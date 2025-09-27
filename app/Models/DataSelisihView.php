<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataSelisihView extends Model
{
    protected $table = "data_selisih_v";
    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
    ];

    public function dataTransaksi(): BelongsTo
    {
        return $this->belongsTo(DataPenerimaanSelisih::class, 'id', 'sumber_id');
    }
}
