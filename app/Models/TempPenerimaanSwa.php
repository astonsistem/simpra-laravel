<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempPenerimaanSwa extends Model
{
    protected $table = "temp_penerimaan_swa";

    protected $fillable = [
        'transaksi_id',
        'sumber_transaksi',
        'akun_id',
        'pihak3',
        'pihak3_alamat',
        'pihak3_telp',
        'uraian',
        'tgl_bayar',
        'no_bayar',
        'total',
        'pendapatan',
        'pdd',
        'piutang',
        'cara_pembayaran',
        'bank_tujuan',
        'admin_kredit',
        'admin_debit',
        'kartubank',
        'no_kartubank',
    ];
}
