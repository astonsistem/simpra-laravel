<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // protected $casts = [
    //     'id' => 'string',
    // ];

    protected $appends = [
        'is_valid',
        'total_jumlah_netto'
    ];


    public function rekeningDpa(): BelongsTo
    {
        return $this->belongsTo(MasterRekeningView::class, 'rek_id', 'rek_id');
    }

    protected function isValid(): Attribute
    {
        return Attribute::make(
            get: fn () => !empty($this->rc_id) && $this->rc_id > 0
        );
    }

    protected function totalJumlahNetto(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => ($this->jumlah ?? 0) - ($this->admin_kredit ?? 0) - ($this->admin_debit ?? 0) + ($this->selisih ?? 0)
        );
    }

}
