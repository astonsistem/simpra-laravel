<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenNonlayanan extends Model
{
    protected $table = "dokumen_nonlayanan";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'tgl',
        'ket',
        'no_dokumen',
        'tgl_dokumen',
        'akun_id',
        'pihak3',
        'pihak3_alamat',
        'pihak3_telp',
        'uraian',
        'tgl_berlaku',
        'tgl_akhir',
        'jatuh_tempo',
        'besaran_per_satuan',
        'total',
        'total_pdd',
        'total_piutang',
        'reklas_pdd',
        'pembayaran_piutang',
        'monev_id',
        'is_web_change',
        'no_putus',
        'tgl_putus',
        'tgl_berlakuputus',
        'nilai_reklasputus',
        'is_buktitagihan',
        'induk_id',
    ];

    // Accessor for terbayar
    public function getTerbayarAttribute()
    {
        return \DB::table('data_penerimaan_lain')
            ->where('piutanglain_id', $this->id)
            ->sum('jumlah_netto');
    }
    // Accessor for sisa_potensi
    public function getSisaPotensiAttribute()
    {
        return $this->total - $this->terbayar;
    }
}
