<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPendapatanPenjamin1 extends Model
{
    protected $table = "data_pendapatan_penjamin1";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'pelayanan_id',
        'pendaftaran_id',
        'no_pendaftaran',
        'tgl_pendaftaran',
        'pasien_id',
        'jenis_tagihan',
        'tgl_krs',
        'tgl_pelayanan',
        'no_rekam_medik',
        'pasien_nama',
        'carabayar_id',
        'carabayar_nama',
        'penjamin_id',
        'penjamin_nama',
        'no_penjamin',
        'tgl_jaminan',
        'instalasi_id',
        'instalasi_nama',
        'kasir_id',
        'kasir_nama',
        'loket_id',
        'loket_nama',
        'total_dijamin',
        'bulan_mrs',
        'bulan_krs',
        'bulan_pelayanan',
        'biaya_admin',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
