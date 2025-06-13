<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPasienBpjs extends Model
{
    protected $table = "infostatuspasienbpjs_v";
    protected $primaryKey = "pendaftaran_id";

    protected $fillable = [
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
        'sep_id',
        'no_sep',
        'tgl_sep',
        'tgl_finalkasir',
        'kasir_id',
        'kasir_nama',
        'loket_id',
        'loket_nama',
        'no_pengajuan_klaim',
        'tgl_pengajuan_klaim',
        'nomor_jaminan',
        'total_tagihan',
        'total_jaminan',
        'status_verifikasi',
        'bulan_mrs',
        'bulan_krs',
        'bulan_pelayanan',
        'total'
    ];
}
