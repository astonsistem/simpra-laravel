<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SyncPendapatanPelayanan extends Model
{
    protected $table = "data_pendapatan_pelayanan";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'pendaftaran_id',
        'tgl_pendaftaran',
        'pasien_id',
        'pasien_alamat',
        'jenis_tagihan',
        'tgl_krs',
        'tgl_pelayanan',
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
        'layanan',
        'obatalkes',
        'penunjang',
        'total',
        'total_dijamin',
        'pendapatan',
        'piutang',
        'pdd',
        'pembayaranpelayanan_id',
        'status_id',
        'bulan_mrs',
        'bulan_krs',
        'bulan_pelayanan',
        'no_pendaftaran',
        'no_rekam_medik',
        'pasien_nama',
        'sync_at ',
        'is_web_change ',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public static function getCountPasienThisYear(): int
    {
        $currentYear = now()->year;

        $result = DB::selectOne("
            SELECT COUNT(DISTINCT pasien_nama) as total
            FROM data_pendapatan_pelayanan
            WHERE EXTRACT(YEAR FROM tgl_pelayanan) = ?
        ", [$currentYear]);

        return $result?->total ?? 0;
    }
}
