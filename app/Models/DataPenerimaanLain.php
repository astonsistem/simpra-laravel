<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPenerimaanLain extends Model
{
    protected $table = "data_penerimaan_lain";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'no_dokumen',
        'tgl_dokumen',
        'akun_id',
        'pihak3',
        'pihak3_alamat',
        'pihak3_telp',
        'uraian',
        'tgl_bayar',
        'no_bayar',
        'sumber_transaksi',
        'transaksi_id',
        'metode_pembayaran',
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
        'sync_at',
        'type',
        'selisih',
        'jumlah_netto',
        'monev_id',
        'rc_id',
        'desc_piutang_pelayanan',
        'desc_piutang_lain',
        'piutang_id',
        'piutanglain_id',
        'is_web_change',
    ];

    protected $casts = [
        'id' => 'string',
    ];
    // akun_data = relationship("AkunModel", foreign_keys=[akun_id])
}
