<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RincianPotensiPelayanan extends Model
{
    protected $table = "rincian_potensi_pelayanan";
    protected $primaryKey = 'rincian_id';
    public $timestamps = false;

    protected $fillable = [
        'piutang_id',
        'pendaftaran_id',
        'total_tagihan',
        'total_klaim',
        'total_verif',
        'total_bayar',
        'jenis',
        'bulan',
        'tahun',
        'penjamin_id',
        'sumber',
        'is_web_change',
        'sep',
        'norm',
        'nama',
        'tgl_mrs',
        'no_dokumen',
    ];
}
