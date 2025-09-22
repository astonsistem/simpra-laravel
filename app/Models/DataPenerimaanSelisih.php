<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPenerimaanSelisih extends Model
{
    use HasFactory;

    protected $table = 'data_penerimaan_selisih';

    protected $fillable = [
        'tgl_bukti',
        'tgl_setor',
        'no_setor',
        'nominal',
        'rek_dpa',
        'loket_kasir',
        'cara_pembayaran',
        'bank',
        'jenis'
    ];
}
