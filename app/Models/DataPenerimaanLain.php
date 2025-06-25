<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataPenerimaanLain extends Model
{
    protected $table = "data_penerimaan_lain";
    protected $keyType = 'string';
    public $incrementing = false;

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

    public function masterAkun()
    {
        return $this->belongsTo(MasterAkun::class, 'akun_id', 'akun_id');
    }

    public static function sumTotal(?string $type = null, ?string $month = null, ?string $sumberTransaksi = null): int
    {
        $query = self::query()
            ->selectRaw('SUM(total) as total');
        if (!empty($month)) {
            $query->whereMonth('tgl_bayar', $month);
        }
        if (!empty($type)) {
            $query->where('type', $type);
        }
        if (!empty($sumberTransaksi)) {
            $query->where('sumber_transaksi', $sumberTransaksi);
        }
        $result = $query->first();

        return $result?->total ?? 0;
    }

    public static function sumTotalByRcId(int $rcId): float
    {
        $result = self::query()
            ->selectRaw('SUM(COALESCE(total,0) - COALESCE(admin_kredit,0) + COALESCE(selisih,0)) as total')
            ->where('rc_id', $rcId)
            ->first();

        return $result?->total ?? 0;
    }
}
