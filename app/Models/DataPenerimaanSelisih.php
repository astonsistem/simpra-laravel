<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPenerimaanSelisih extends Model
{
    protected $table = "data_penerimaan_selisih";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'created_at',
        'updated_at',
        'tgl_setor',
        'tgl_bukti',
        'tandabuktibayar_id',
        'no_buktibayar',
        'tgl_buktibayar',
        'cara_pembayaran',
        'bank_tujuan',
        'kartubank',
        'no_kartubank',
        'kasir_id',
        'kasir_nama',
        'loket_id',
        'loket_nama',
        'nilai',
        'klasifikasi',
        'jumlah',
        'admin_kredit',
        'admin_debit',
        'selisih',
        'jumlah_netto',
        'rc_id',
        'bku_id',
        'akun_id',
        'rek_id',
        'jenis',
        'sumber_transaksi',
        'penyetoran',
        'sumber_id',
    ];
}
