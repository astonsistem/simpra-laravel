<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterStatus extends Model
{
    protected $table = "master_status";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'status_id',
        'status_nama',
        'status_ket',
        'status_app',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    /**
     * edit	1	Tagihan
    edit	2	Klaim
    edit	3	Verif
    edit	4	Terima
    edit	5	Setor
    edit	6	BKU
    edit	7	Pending
    edit	8	Gagal
    edit	-1	Batal
     */

    public const STATUS = [
        '-1' => 'Batal',
        '1' => 'Tagihan',
        '2' => 'Klaim',
        '3' => 'Verif',
        '4' => 'Terima',
        '5' => 'Setor',
        '6' => 'BKU',
        '7' => 'Pending',
        '8' => 'Gagal',
    ];
}
