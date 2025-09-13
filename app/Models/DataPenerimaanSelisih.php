<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPenerimaanSelisih extends Model
{
    use HasFactory;

    protected $table = 'data_penerimaan_selisih';

    protected $fillable = [
        'id',
        'admin_debit',
        'admin_kredit',
        'akun_id',
        'bank_tujuan',
        'bku_id',
        'cara_pembayaran',
        'jenis',
        'jumlah',
        'jumlah_netto',
        'kartubank',
        'kasir_id',
        'kasir_nama',
        'klasifikasi',
        'loket_id',
        'loket_nama',
        'nilai',
        'no_buktibayar',
        'no_kartubank',
        'penyetor',
        'rc_id',
        'rek_id',
        'selisih',
        'sumber_id',
        'sumber_transaksi',
        'tandabuktibayar_id',
        'tgl_bukti',
        'tgl_buktibayar',
        'tgl_setor',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    protected $appends = [
        'total_jumlah_netto'
    ];

    protected function totalJumlahNetto(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => ($this->jumlah ?? 0) - ($this->admin_kredit ?? 0) - ($this->admin_debit ?? 0) + ($this->selisih ?? 0)
        );
    }

}
