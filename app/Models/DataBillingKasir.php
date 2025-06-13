<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBillingKasir extends Model
{
    protected $table = "data_billing_kasir";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'pendaftaran_id',
        'no_pendaftaran',
        'tgl_pendaftaran',
        'pasien_id',
        'no_rekam_medik',
        'pasien_nama',
        'pasien_alamat',
        'jenis_tagihan',
        'tgl_krs',
        'tgl_pelayanan',
        'carabayar_id',
        'carabayar_nama',
        'penjamin_id',
        'penjamin_nama',
        'instalasi_id',
        'instalasi_nama',
        'metode_bayar',
        'tandabuktibayar_id',
        'no_buktibayar',
        'tgl_buktibayar',
        'sep_id',
        'no_sep',
        'tgl_sep',
        'cara_pembayaran',
        'bank_tujuan',
        'admin_kredit',
        'admin_debit',
        'kartubank_pasien',
        'no_kartubank_pasien',
        'closingkasir_id',
        'tgl_closingkasir',
        'no_closingkasir',
        'kasir_id',
        'kasir_nama',
        'loket_id',
        'loket_nama',
        'guna_bayar',
        'total',
        'klasifikasi',
        'status_id',
        'bulan_mrs',
        'bulan_krs',
        'bulan_pelayanan',
        'sync_at',
        'monev_id',
        'status',
        'selisih',
        'jumlah_netto'
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
