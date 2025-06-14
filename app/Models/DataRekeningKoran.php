<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRekeningKoran extends Model
{
    protected $table = "data_rekening_koran";
    protected $primaryKey = "rc_id";

    protected $fillable = [
        'rc_id',
        'tgl',
        'ket',
        'no_rc',
        'tgl_rc',
        'rek_dari',
        'nama_dari',
        'akun_id',
        'akunls_id',
        'uraian',
        'bku_id',
        'no_bku',
        'ket_bku',
        'klarif_lain',
        'klarif_layanan',
        'debit',
        'kredit',
        'klarif_admin',
        'kunci',
        'pb',
        'mutasi',
        'bank',
        'pb_dari',
        'file_upload',
        'sync_at',
        'status',
        'is_web_change',
    ];
}
