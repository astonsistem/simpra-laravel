<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPotensiPelayanan extends Model
{
    protected $table = "data_potensi_pelayanan";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

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
        'tgl_pelayanan',
        'instalasi_id',
        'instalasi_nama',
        'carabayar_id',
        'carabayar_nama',
        'penjamin_id',
        'penjamin_nama',
        'no_pengajuan',
        'tgl_pengajuan',
        'no_dokumen',
        'tgl_dokumen',
        'uraian',
        'total_pengajuan',
        'total',
        'status_id',
        'akun_id',
        'sync_at',
        'monev_id',
        'pelayanan_id',
        'is_web_change',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    // carabayar_data = relationship("CaraBayarModel", foreign_keys=[carabayar_id])
    // penjamin_data = relationship("PenjaminModel", foreign_keys=[penjamin_id])

    // Accessor for terbayar
    public function getTerbayarAttribute()
    {
        return \DB::table('data_penerimaan_lain')
            ->where('piutang_id', $this->id)
            ->sum('jumlah_netto');
    }
    // Accessor for sisa_potensi
    public function getSisaPotensiAttribute()
    {
        return $this->total - $this->terbayar;
    }
}
